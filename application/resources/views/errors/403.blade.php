<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-50 text-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 · เข้าสู่ระบบเพื่อความปลอดภัย — CED Construction Executive Dashboard</title>

    <!-- Google Fonts for Modern Clean Readability -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-100 text-slate-900 flex items-center justify-center p-4 selection:bg-[#FF6B00] selection:text-white">
    <div class="w-full max-w-lg space-y-6 text-center">
        <!-- Logo & Brand Badge -->
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-[#FF6B00] to-amber-600 shadow-lg shadow-orange-500/20 text-white text-3xl font-black mx-auto">
            🏗️
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/90 p-8 sm:p-10 shadow-2xl space-y-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-50 border border-orange-200 text-xs font-extrabold text-[#EA580C]">
                <span>🔒</span>
                <span>AUTHENTICATION & RBAC REQUIRED</span>
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    ต้องเข้าสู่ระบบก่อนใช้งาน
                </h1>
                <p class="text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
                    ระบบ Construction Executive Dashboard เป็นระบบบริหารโครงการก่อสร้างระดับผู้บริหารที่มีการรักษาความปลอดภัยตามมาตรฐาน หากยังไม่ได้เข้าสู่ระบบ กรุณาเข้าสู่ระบบผ่านปุ่มด้านล่าง
                </p>
            </div>

            <!-- Login Action CTA Button -->
            <div class="pt-2 space-y-3">
                <a href="{{ route('login') }}" 
                   class="flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-2xl bg-[#FF6B00] hover:bg-[#E05E00] text-white font-extrabold text-sm shadow-md shadow-orange-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>ไปที่หน้าเข้าสู่ระบบ (1-Click Demo Login)</span>
                </a>

                <div class="text-xs text-slate-400 font-medium">
                    มีโหมด 1-Click Demo สำหรับทดสอบทุกบทบาท (Project Director, QS, PM, Site Engineer)
                </div>
            </div>
        </div>

        <p class="text-xs text-slate-400 font-medium">
            CED · Construction Executive Dashboard & Commercial Execution System
        </p>
    </div>
</body>
</html>
