<?php

namespace App\Models;

use Corcel\Model\Post as Corcel;
use Tbruckmaier\Corcelacf\Models\Text;
use Tbruckmaier\Corcelacf\AcfTrait;

class Post extends Corcel
{
    use AcfTrait;

    protected $postType = 'post';

    public static function boot()
    {
        self::addAcfRelations([
            'fen_code' => [
                'key' => 'fen_code',
                'label' => 'FEN Code',
                'name' => 'fen_code',
                'type' => 'text',
            ],
        ]);
        parent::boot();
    }
}