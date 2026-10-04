<?php

namespace Tests\Unit\Actions;

use App\Actions\Game\CalculateEloRatingsAction;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CalculateEloRatingsActionTest extends TestCase
{
    private CalculateEloRatingsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new CalculateEloRatingsAction();
    }

    public static function resultProvider(): array
    {
        return [
            'player 1 wins' => [1],
            'player 2 wins' => [-1],
            'draw' => [0],
            'unknown result is treated as draw' => [99],
        ];
    }

    public function test_equal_ratings_change_symmetrically_after_a_win(): void
    {
        [$player1, $player2] = $this->action->execute(1200, 1200, 1);

        $this->assertEquals(1210, $player1);
        $this->assertEquals(1190, $player2);
    }

    public function test_equal_ratings_change_symmetrically_after_a_loss(): void
    {
        [$player1, $player2] = $this->action->execute(1200, 1200, -1);

        $this->assertEquals(1190, $player1);
        $this->assertEquals(1210, $player2);
    }

    public function test_equal_ratings_do_not_change_after_a_draw(): void
    {
        [$player1, $player2] = $this->action->execute(1200, 1200, 0);

        $this->assertEquals(1200, $player1);
        $this->assertEquals(1200, $player2);
    }

    #[DataProvider('resultProvider')]
    public function test_result_always_returns_two_numeric_ratings(int $result): void
    {
        [$player1, $player2] = $this->action->execute(1500, 1400, $result);

        $this->assertCount(2, [$player1, $player2]);
        $this->assertIsNumeric($player1);
        $this->assertIsNumeric($player2);
    }
}
