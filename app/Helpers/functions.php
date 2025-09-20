<?php

use Illuminate\Support\Facades\NumberFormat;

function numberToWordsVi($number)
{
    $formatter = new NumberFormatter('vi_VN', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function numberToWordsEn($number)
{
    $formatter = new NumberFormatter('en_US', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function numberToWordsJa($number)
{
    $formatter = new NumberFormatter('ja_JP', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function numberToWordsKo($number)
{
    $formatter = new NumberFormatter('ko_KR', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}

function numberToWordsZh($number)
{
    $formatter = new NumberFormatter('zh_CN', NumberFormatter::SPELLOUT);
    return $formatter->format($number);
}