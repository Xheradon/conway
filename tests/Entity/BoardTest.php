<?php

namespace Conway\Tests\Entity;

use Conway\Entity\Board;
use Conway\Exception\InvalidColException;
use Conway\Exception\InvalidHeightException;
use Conway\Exception\InvalidRowException;
use Conway\Exception\InvalidWidthException;
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

        new Board(0);
    }

    public function testInvalidInitialHeightThrowsException(): void
    {
        $this->expectException(InvalidHeightException::class);

        Board::createFromInitialRows([]);
    }

    public function testDefaultWidthIsGreaterThanZero(): void
    {
        $b = new Board();
        $this->assertGreaterThan(0, $b->getWidth());
    }

    public function testInvalidWidthThrowsException(): void
    {
        $this->expectException(InvalidWidthException::class);

        new Board(5, 0);
    }

    public function testInvalidInitialWidthThrowsException(): void
    {
        $this->expectException(InvalidWidthException::class);

        Board::createFromInitialRows([[]]);
    }

    public function testRowsWithDifferentWidthThrowsException(): void
    {
        $this->expectException(InvalidWidthException::class);

        Board::createFromInitialRows([[0,0],[0]]);
    }

    public function testInitialRowsAreCorrectlySet(): void
    {
        $initialMatrix = [
            [0, 1],
            [0, 1]
        ];
        $b = Board::createFromInitialRows($initialMatrix);
        $this->assertEquals($initialMatrix, $b->rows);
    }

    public function testRowZeroColZeroHasExaclyThreeNeighboursFinite(): void
    {
        $initialMatrix = [
            [1, 1, 0],
            [1, 1, 0],
            [0, 0, 0]
        ];

        $b = Board::createFromInitialRows($initialMatrix);

        $method = new ReflectionMethod($b, 'countActiveNeighboursFinite');
        $method->setAccessible(true);
        $activeNeighbours = $method->invokeArgs($b, [0, 0]);
        $this->assertEquals(3, $activeNeighbours);
    }

    public function testRowZeroColZeroHasExactlyThreeNeighboursInifinite(): void
    {
        $initialMatrix = [
            [1, 0, 1],
            [0, 0, 0],
            [1, 1, 0]
        ];

        $b = Board::createFromInitialRows($initialMatrix);

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

        $initialGeneration = Board::createFromInitialRows($initialMatrix);
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

        $initialGeneration = Board::createFromInitialRows($initialMatrix);
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

        $initialGeneration = Board::createFromInitialRows($initialMatrix);
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

        $initialGeneration = Board::createFromInitialRows($initialMatrix);
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

        $initialGeneration = Board::createFromInitialRows($initialMatrix);
        $firstGeneration = Board::createFromPreviousGeneration($initialGeneration);

        $this->assertEquals(1, $firstGeneration->rows[0][0]);
    }
}
