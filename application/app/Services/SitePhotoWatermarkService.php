<?php

namespace App\Services;

use App\Models\EvidenceDocument;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SitePhotoWatermarkService
{
    public function __construct(
        private readonly AuditRecorder $auditRecorder
    ) {}

    /**
     * Stamped physical visual watermark onto image bytes using GD.
     *
     * @param  array<string, mixed>  $telemetry
     * @return array{bytes: string, mime: string}
     */
    public function applyWatermark(string $imageBytes, string $mimeType, array $telemetry): array
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatefromstring')) {
            return ['bytes' => $imageBytes, 'mime' => $mimeType];
        }

        try {
            $image = @imagecreatefromstring($imageBytes);
            if (! $image) {
                return ['bytes' => $imageBytes, 'mime' => $mimeType];
            }

            $width = imagesx($image);
            $height = imagesy($image);

            if ($width < 100 || $height < 100) {
                imagedestroy($image);

                return ['bytes' => $imageBytes, 'mime' => $mimeType];
            }

            // Height of watermark overlay banner (scales moderately with image resolution)
            $bannerHeight = max(48, (int) round($height * 0.08));
            $bannerY = $height - $bannerHeight;

            // Enable alpha blending
            imagealphablending($image, true);
            imagesavealpha($image, true);

            // Draw semi-transparent dark banner (rgba(15, 23, 42, 0.85))
            $bannerBg = imagecolorallocatealpha($image, 15, 23, 42, 20);
            imagefilledrectangle($image, 0, $bannerY, $width, $height, $bannerBg);

            // Draw border line above banner
            $borderAccent = imagecolorallocatealpha($image, 245, 158, 11, 40); // amber
            imageline($image, 0, $bannerY, $width, $bannerY, $borderAccent);

            // Colors for text
            $colorBrand = imagecolorallocate($image, 251, 191, 36); // amber-400
            $colorText = imagecolorallocate($image, 255, 255, 255); // white
            $colorMuted = imagecolorallocate($image, 203, 213, 225); // slate-300

            // Text formatting
            $category = strtoupper((string) ($telemetry['category'] ?? 'PROGRESS'));
            $projectCode = (string) ($telemetry['project_code'] ?? 'CED-SITE');
            $refText = (string) ($telemetry['ref_text'] ?? 'DAILY INSPECTION');
            $gpsText = isset($telemetry['lat'], $telemetry['lng']) && $telemetry['lat'] !== null && $telemetry['lng'] !== null
                ? sprintf('GPS: %.6f, %.6f', (float) $telemetry['lat'], (float) $telemetry['lng'])
                : 'GPS: TELEMETRY PENDING';
            $timeText = (string) ($telemetry['timestamp'] ?? Carbon::now()->format('Y-m-d H:i:s T'));
            $userText = (string) ($telemetry['inspector_name'] ?? 'SITE SUPERVISOR');

            $line1 = "[CED-INSPECT] {$category} | {$projectCode} | {$refText}";
            $line2 = "{$gpsText} | {$timeText} | BY: {$userText}";

            // Draw text lines
            $font = 4; // built-in GD font
            $lineSpacing = 18;
            $yOffset = $bannerY + 8;

            imagestring($image, $font, 12, $yOffset, $line1, $colorBrand);
            imagestring($image, 3, 12, $yOffset + $lineSpacing, $line2, $colorMuted);

            // Export watermarked buffer
            ob_start();
            if (str_contains($mimeType, 'png')) {
                imagepng($image, null, 8);
                $finalMime = 'image/png';
            } elseif (str_contains($mimeType, 'webp') && function_exists('imagewebp')) {
                imagewebp($image, null, 85);
                $finalMime = 'image/webp';
            } else {
                imagejpeg($image, null, 85);
                $finalMime = 'image/jpeg';
            }
            $watermarkedBytes = (string) ob_get_clean();
            imagedestroy($image);

            return [
                'bytes' => $watermarkedBytes,
                'mime' => $finalMime,
            ];
        } catch (Throwable) {
            return ['bytes' => $imageBytes, 'mime' => $mimeType];
        }
    }

    /**
     * Store site photo with watermark, compute SHA-256, and persist immutable EvidenceDocument.
     *
     * @param  array{
     *     task_id?: string|null,
     *     constraint_id?: string|null,
     *     caption?: string|null,
     *     category?: string|null,
     *     gps_latitude?: float|string|null,
     *     gps_longitude?: float|string|null,
     *     captured_at?: string|Carbon|null
     * }  $attributes
     */
    public function storeSitePhoto(
        Project $project,
        User $uploader,
        UploadedFile $uploadedFile,
        array $attributes = []
    ): EvidenceDocument {
        $disk = Storage::disk('evidence');
        $rawBytes = $uploadedFile->get();
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($rawBytes);

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException("Unsupported image MIME type: {$mimeType}");
        }

        $taskId = ! empty($attributes['task_id']) ? (string) $attributes['task_id'] : null;
        $constraintId = ! empty($attributes['constraint_id']) ? (string) $attributes['constraint_id'] : null;
        $caption = ! empty($attributes['caption']) ? substr((string) $attributes['caption'], 0, 255) : null;
        $category = ! empty($attributes['category']) ? (string) $attributes['category'] : 'PROGRESS';
        $gpsLat = isset($attributes['gps_latitude']) && $attributes['gps_latitude'] !== '' && $attributes['gps_latitude'] !== null
            ? (float) $attributes['gps_latitude']
            : null;
        $gpsLng = isset($attributes['gps_longitude']) && $attributes['gps_longitude'] !== '' && $attributes['gps_longitude'] !== null
            ? (float) $attributes['gps_longitude']
            : null;
        $capturedAt = isset($attributes['captured_at']) && $attributes['captured_at']
            ? Carbon::parse($attributes['captured_at'])
            : Carbon::now();

        // Resolve reference text for visual watermark
        $refText = 'GENERAL PROGRESS';
        if ($taskId) {
            $task = WbsTask::query()->where('project_id', $project->id)->find($taskId);
            if ($task) {
                $refText = "TASK {$task->code}: {$task->name}";
            }
        } elseif ($constraintId) {
            $constraint = ProjectConstraint::query()->where('project_id', $project->id)->find($constraintId);
            if ($constraint) {
                $refText = "CONSTRAINT [{$constraint->type}]: {$constraint->title}";
            }
        }

        // Apply physical visual watermark
        $watermarked = $this->applyWatermark($rawBytes, $mimeType, [
            'category' => $category,
            'project_code' => $project->code ?? 'CED-PRJ',
            'ref_text' => Str::limit($refText, 45),
            'lat' => $gpsLat,
            'lng' => $gpsLng,
            'timestamp' => $capturedAt->format('Y-m-d H:i:s T'),
            'inspector_name' => $uploader->name,
        ]);

        $finalBytes = $watermarked['bytes'];
        $finalMime = $watermarked['mime'];
        $sha256 = hash('sha256', $finalBytes);
        $documentId = (string) Str::ulid();

        $extension = match ($finalMime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $storagePath = $project->getKey().'/site_photos/'.$documentId.'.'.$extension;
        $written = false;

        return DB::transaction(function () use (
            $caption,
            $capturedAt,
            $category,
            $constraintId,
            $disk,
            $documentId,
            $finalBytes,
            $finalMime,
            $gpsLat,
            $gpsLng,
            $project,
            $refText,
            $sha256,
            $storagePath,
            $taskId,
            $uploadedFile,
            $uploader,
            &$written
        ) {
            if (! $disk->put($storagePath, $finalBytes)) {
                throw new RuntimeException('Failed to store site photo onto evidence disk.');
            }
            $written = true;

            $originalName = basename(str_replace('\\', '/', $uploadedFile->getClientOriginalName()));
            $originalName = substr($originalName !== '' ? $originalName : 'site_photo.jpg', 0, 255);

            $evidence = EvidenceDocument::query()->create([
                'id' => $documentId,
                'project_id' => $project->getKey(),
                'wbs_task_id' => $taskId,
                'constraint_id' => $constraintId,
                'storage_disk' => 'evidence',
                'storage_path' => $storagePath,
                'original_name' => $originalName,
                'caption' => $caption,
                'gps_latitude' => $gpsLat,
                'gps_longitude' => $gpsLng,
                'captured_at' => $capturedAt,
                'mime_type' => $finalMime,
                'byte_size' => strlen($finalBytes),
                'sha256' => $sha256,
                'access_class' => 'PRIVATE',
                'review_state' => 'UPLOADED',
                'metadata' => [
                    'category' => $category,
                    'reference' => $refText,
                    'watermarked' => true,
                    'device' => 'MOBILE_QUICK_CAPTURE',
                ],
                'uploaded_by' => $uploader->getKey(),
            ]);

            $this->auditRecorder->record(
                $project->getKey(),
                $uploader,
                'evidence.site_photo_uploaded',
                $evidence,
                [
                    'sha256' => $sha256,
                    'wbs_task_id' => $taskId,
                    'constraint_id' => $constraintId,
                    'category' => $category,
                    'gps_latitude' => $gpsLat,
                    'gps_longitude' => $gpsLng,
                    'captured_at' => $capturedAt->toISOString(),
                ]
            );

            return $evidence;
        });
    }
}
