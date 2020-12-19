<?php

namespace Conway\Tests\Entity;

use Conway\Entity\Board;
use Conway\Exception\HeightMismatchException;
use Conway\Exception\InvalidColException;
use Conway\Exception\InvalidHeightException;
use Conway\Exception\InvalidRowException;
use Conway\Exception\InvalidWidthException;
use Conway\Exception\WidthMismatchException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class BoardTest extends TestCase
{
    public function testDefaultHeightIsGreaterThanZero(): void
    {
        $b = new Board();
        $this->assertGreaterThan(0, $b->getHeight());
    }

    public function testInvalidHeightThrowsException(): void
    {
        $this->expectException(InvalidHeightException::class);

        $b = new Board(0);
    }

    public function testHeightMismatchThrowsException(): void
    {
        $this->expectException(HeightMismatchException::class);

        $b = new Board(2, 1, [[0]]);
    }

    public function testDefaultWidthIsGreaterThanZero(): void
    {
        $b = new Board();
        $this->assertGreaterThan(0, $b->getWidth());
    }

    public function testInvalidWidthThrowsException(): void
    {
        $this->expectException(InvalidWidthException::class);

        $b = new Board(5, 0);
    }

    public function testWidthMismatchThrowsException(): void
    {
        $this->expectException(WidthMismatchException::class);

        $b = new Board(1, 2, [[0]]);
    }

    public function testInitialRowsAreCorrectlySet(): void
    {
        $initialRows = [[0, 1], [0, 1]];
        $b = new Board(2, 2, $initialRows);
        $this->assertEquals($initialRows, $b->rows);
    }

    public function testRowZeroColZeroHasExaclyThreeNeighboursFinite(): void
    {
        $initialRows = [
            [1, 1, 0],
            [1, 1, 0],
            [0, 0, 0]
        ];

        $b = new Board(3, 3, $initialRows);

        $method = new ReflectionMethod($b, 'countActiveNeighboursFinite');
        $method->setAccessible(true);
        $activeNeighbours = $method->invokeArgs($b, [0, 0]);
        $this->assertEquals(3, $activeNeighbours);
    }

    public function testRowZeroColZeroHasExactlyThreeNeighboursInifinite(): void
    {
        $initialRows = [
            [1, 0, 1],
            [0, 0, 0],
            [1, 1, 0]
        ];

        $b = new Board(3, 3, $initialRows);

        $method = new ReflectionMethod($b, 'countActiveNeighbours');
        $method->setAccessible(true);
        $activeNeighbours = $method->invokeArgs($b, [0, 0]);
        $this->assertEquals(3, $activeNeighbours);
    }

    public function testInvalidRowIndexAccessThrowsInvalidRowException(): void
    {
        $this->expectException(InvalidRowException::class);

        $b = new Board(1, 1);

        $method = new ReflectionMethod($b, 'checkIndexIsValid');
        $method->setAccessible(true);
        $method->invokeArgs($b, [5, 0]);
    }

    public function testInvalidHeightAccessThrowsInvalidColException(): void
    {
        $this->expectException(InvalidColException::class);

        $b = new Board(1, 1);

        $method = new ReflectionMethod($b, 'checkIndexIsValid');
        $method->setAccessible(true);
        $method->invokeArgs($b, [0, 1]);
    }

    public function testLiveCellWithTwoNeighboursLivesOn(): void
    {
        $initialMatrix = [
            [1, 1, 0],
            [1, 0, 0],
            [0, 0, 0],
        ];

        $initialGeneration = new Board(3, 3, $initialMatrix);
        $firstGeneration = Board::createFromPreviousGeneration($initialGeneration);

        $this->assertEquals(1, $firstGeneration->rows[0][0]);
    }

    public function testLiveCellWithThreeNeighboursLivesOn(): void
    {
        $initialMatrix = [
            [1, 1, 0],
            [1, 1, 0],
            [0, 0, 0],
        ];

        $initialGeneration = new Board(3, 3, $initialMatrix);
        $firstGeneration = Board::createFromPreviousGeneration($initialGeneration);

        $this->assertEquals(1, $firstGeneration->rows[0][0]);
    }

    public function testLiveCellWithFewerThanTwoLiveNeighboursDies(): void
    {
        $initialMatrix = [
            [1, 1, 0],
            [0, 0, 0],
            [0, 0, 0],
        ];

        $initialGeneration = new Board(3, 3, $initialMatrix);
        $firstGeneration = Board::createFromPreviousGeneration($initialGeneration);

        $this->assertEquals(0, $firstGeneration->rows[0][0]);
    }

    public function testLiveCellWithMoreThanThreeLiveNeighboursDies(): void
    {

        $initialMatrix = [
            [1, 1, 1],
            [1, 1, 1],
            [1, 1, 1],
        ];

        $initialGeneration = new Board(3, 3, $initialMatrix);
        $firstGeneration = Board::createFromPreviousGeneration($initialGeneration);

        $this->assertEquals(0, $firstGeneration->rows[1][1]);
    }

    public function testDeadCellWithThreeNeighboursBecomesAlive(): void
    {
        $initialMatrix = [
            [0, 1, 0, 0],
            [1, 1, 0, 0],
            [0, 0, 0, 0],
            [0, 0, 0, 0]
        ];

        $initialGeneration = new Board(4, 4, $initialMatrix);
        $firstGeneration = Board::createFromPreviousGeneration($initialGeneration);

        $this->assertEquals(1, $firstGeneration->rows[0][0]);
    }
}
