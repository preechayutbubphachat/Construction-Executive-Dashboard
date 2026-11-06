<?php

namespace App\Domain\Finance;

enum FinanceStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';
    case Approved = 'APPROVED';
    case Posted = 'POSTED';
}
