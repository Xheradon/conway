<?php


namespace Conway\Entity;

use Conway\Exception\InvalidColException;
use Conway\Exception\InvalidHeightException;
use Conway\Exception\InvalidRowException;
use Conway\Exception\InvalidWidthException;

class Board
{
    private int $height;
    private int $width;
    public array $rows;

    /**
     * Board constructor.
     * @param int $height of the matrix
     * @param int $width of the matrix
     * @param bool $random enables or disables random generation of rows
     * @throws InvalidHeightException when height < 1
     * @throws InvalidWidthException when width < 1
     */
    public function __construct(int $height = 5, int $width = 5, bool $random = true)
    {
        $this->setHeight($height);
        $this->setWidth($width);

        if ($random) {
            // initialize the board with random values until we fill it
            for ($x = 0; $x < $this->height; $x++) {
                $row = [];
                for ($y = 0; $y < $this->width; $y++) {
                    $row[] = rand(0, 1);
                }
                $this->rows[$x] = $row;
            }
        }
    }

    public static function createFromInitialRows(array $initialRows): Board
    {
        if (($height = \count($initialRows)) < 1)
            throw new InvalidHeightException();

        if (($width = \count($initialRows[0])) < 1)
            throw new InvalidWidthException();

        $board = new Board($height, $width, false);
        $board->rows = $initialRows;
        return $board;
    }

    /**
     * Any live cell with fewer than two live neighbours dies, as if caused by under-population.
     * Any live cell with two or three live neighbours lives on to the next generation.
     * Any live cell with more than three live neighbours dies, as if by overcrowding.
     * Any dead cell with exactly three live neighbours becomes a live cell, as if by reproduction.
     *
     *
     * @param Board $previousBoard
     * @param bool $loop
     * @return Board
     * @throws InvalidColException
     * @throws InvalidHeightException
     * @throws InvalidRowException
     * @throws InvalidWidthException
     */
    public static function createFromPreviousGeneration(Board $previousBoard, bool $loop = false): Board
    {
        $rows = \range(0, $previousBoard->getHeight() - 1);
        foreach ($previousBoard->rows as $rowIndex => $row) {
            $rows[$rowIndex] = \range(0, $previousBoard->getWidth() - 1);
            foreach ($row as $colIndex => $col) {
                $activeNeightbours = $loop
                    ? $previousBoard->countActiveNeighbours($rowIndex, $colIndex)
                    : $previousBoard->countActiveNeighboursFinite($rowIndex, $colIndex);

                if ($col) { // live cells
                    if (\in_array($activeNeightbours, [2, 3])) // Any live cell with two or three live neighbours lives on to the next generation.
                        $col = 1;
                    else // Any live cell with fewer than two live neighbours dies, as if caused by under-population. Any live cell with more than three live neighbours dies, as if by overcrowding.
                        $col = 0;
                } elseif ($activeNeightbours === 3) { // Any dead cell with exactly three live neighbours becomes a live cell, as if by reproduction.
                    $col = 1;
                }

                $rows[$rowIndex][$colIndex] = $col;
            }
        }

        return Board::createFromInitialRows($rows);
    }

    /**
     * @param int $height
     * @return $this
     * @throws InvalidHeightException when height < 1
     */
    public function setHeight(int $height): self
    {
        if ($height < 1) throw new InvalidHeightException();

        $this->height = $height;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $width
     * @return $this
     * @throws InvalidWidthException when width < 1
     */
    public function setWidth(int $width): self
    {
        if ($width < 1) throw new InvalidWidthException();

        $this->width = $width;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Counts the number of active neighbours in an infinite matrix, that is, top-bottom and left-right are connected
     *
     * Each cell has 8 neighbours:
     *      N   N   N
     *      N   C   N
     *      N   N   N
     *
     * @param int $row
     * @param int $col
     * @return int the number of active neighbours (alive = +1, dead = +0)
     * @throws InvalidColException when the column is not in the matrix
     * @throws InvalidRowException when the row is not in the matrix
     */
    protected function countActiveNeighbours(int $row, int $col): int
    {
        $this->checkIndexIsValid($row, $col);

        $topIndex = $row === 0 ? $this->height - 1 : $row - 1;
        $leftIndex = $col === 0 ? $this->width - 1 : $col - 1;
        $bottomIndex = $row === $this->height - 1 ? 0 : $row + 1;
        $rightIndex = $col === $this->width - 1 ? 0 : $col + 1;

        return
            $this->rows[$topIndex][$leftIndex]
            + $this->rows[$topIndex][$col]
            + $this->rows[$topIndex][$rightIndex]
            + $this->rows[$row][$leftIndex]
            + $this->rows[$row][$rightIndex]
            + $this->rows[$bottomIndex][$leftIndex]
            + $this->rows[$bottomIndex][$col]
            + $this->rows[$bottomIndex][$rightIndex];

    }

    /**
     * Counts the number of active neighbours in a finite matrix
     *
     * Each cell has 8 neighbours:
     *      N   N   N
     *      N   C   N
     *      N   N   N
     *
     * @param int $row
     * @param int $col
     * @return int the number of active neighbours (alive = +1, dead = +0)
     * @throws InvalidColException when the column is not in the matrix
     * @throws InvalidRowException when the row is not in the matrix
     */
    protected function countActiveNeighboursFinite(int $row, int $col): int
    {
        $this->checkIndexIsValid($row, $col);

        $topIndex = $row - 1;
        $leftIndex = $col - 1;
        $bottomIndex = $row + 1;
        $rightIndex = $col + 1;

        $topLeft = \array_key_exists($topIndex, $this->rows) && \array_key_exists($leftIndex, $this->rows[$topIndex])
            ? $this->rows[$topIndex][$leftIndex] : 0;
        $top = \array_key_exists($topIndex, $this->rows) ? $this->rows[$topIndex][$col] : 0;
        $topRight = \array_key_exists($topIndex, $this->rows) && \array_key_exists($rightIndex, $this->rows[$topIndex])
            ? $this->rows[$topIndex][$rightIndex] : 0;
        $left = \array_key_exists($leftIndex, $this->rows[$row]) ? $this->rows[$row][$leftIndex] : 0;
        $right = \array_key_exists($rightIndex, $this->rows[$row]) ? $this->rows[$row][$rightIndex] : 0;
        $bottomLeft = \array_key_exists($bottomIndex, $this->rows) && \array_key_exists($leftIndex, $this->rows[$bottomIndex])
            ? $this->rows[$bottomIndex][$leftIndex] : 0;
        $bottom = \array_key_exists($bottomIndex, $this->rows) ? $this->rows[$bottomIndex][$col] : 0;
        $bottomRight = \array_key_exists($bottomIndex, $this->rows) && \array_key_exists($rightIndex, $this->rows[$bottomIndex])
            ? $this->rows[$bottomIndex][$rightIndex] : 0;

        return $topLeft + $top + $topRight + $left + $right + $bottomLeft + $bottom + $bottomRight;
    }

    /**
     * @param int $row
     * @param int $col
     * @throws InvalidColException when the column doesn't exist
     * @throws InvalidRowException when the row doesn't exist
     */
    private function checkIndexIsValid(int $row, int $col): void
    {
        if ($row < 0 || $row >= $this->height)
            throw new InvalidRowException();

        if ($col < 0 || $col >= $this->width)
            throw new InvalidColException();
    }
}