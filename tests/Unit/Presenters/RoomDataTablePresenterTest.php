<?php

namespace Tests\Unit\Presenters;

use App\Http\Controllers\RoomController;
use App\Presenters\RoomDataTablePresenter;
use stdClass;
use Tests\TestCase;

class RoomDataTablePresenterTest extends TestCase
{
    public function test_format_turn_detects_red_from_fen(): void
    {
        $presenter = new RoomDataTablePresenter('en');

        $row = (object) [
            'fen' => RoomController::INITIAL_FEN,
        ];

        $html = $presenter->formatTurn($row);

        $this->assertStringContainsString('Red', $html);
        $this->assertStringContainsString('badge-status', $html);
    }

    public function test_format_turn_detects_black_from_fen(): void
    {
        $presenter = new RoomDataTablePresenter('en');

        $row = (object) [
            'fen' => 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR b - - 0 1',
        ];

        $html = $presenter->formatTurn($row);

        $this->assertStringContainsString('Black', $html);
    }

    public function test_format_result_maps_game_results_to_translated_labels(): void
    {
        $presenter = new RoomDataTablePresenter('en');

        foreach ([
            '-1' => 'Guest won',
            '0' => 'Draw',
            '1' => 'Host won',
        ] as $result => $label) {
            $row = (object) [
                'result' => $result,
                'red_time' => 600,
                'black_time' => 600,
                'fen' => 'some-active-fen',
            ];

            $this->assertStringContainsString($label, $presenter->formatResult($row));
        }
    }

    public function test_format_result_prioritizes_a_red_timeout(): void
    {
        $presenter = new RoomDataTablePresenter('en');

        $row = (object) [
            'result' => null,
            'red_time' => 0,
            'black_time' => 600,
            'fen' => 'some-active-fen',
        ];

        $this->assertStringContainsString('Guest won', $presenter->formatResult($row));
    }

    public function test_format_result_prioritizes_a_black_timeout(): void
    {
        $presenter = new RoomDataTablePresenter('en');

        $row = (object) [
            'result' => null,
            'red_time' => 600,
            'black_time' => 0,
            'fen' => 'some-active-fen',
        ];

        $this->assertStringContainsString('Host won', $presenter->formatResult($row));
    }

    public function test_format_result_marks_initial_fen_as_not_started(): void
    {
        $presenter = new RoomDataTablePresenter('en');

        $row = (object) [
            'result' => null,
            'red_time' => 600,
            'black_time' => 600,
            'fen' => RoomController::INITIAL_FEN,
        ];

        $this->assertStringContainsString('Not started', $presenter->formatResult($row));
    }

    public function test_format_result_marks_non_initial_position_without_result_as_ongoing(): void
    {
        $presenter = new RoomDataTablePresenter('en');

        $row = (object) [
            'result' => null,
            'red_time' => 600,
            'black_time' => 600,
            'fen' => 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR b - - 1 1',
        ];

        $this->assertStringContainsString('Ongoing', $presenter->formatResult($row));
    }
}
