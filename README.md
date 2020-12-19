# Conway's Game of Life
![running gif](https://github.com/Xheradon/conway/blob/main/run.gif?raw=true)

## Game Rules
The universe of the Game of Life is an infinite two-dimensional orthogonal grid of square cells, each of which is in one of two possible states: alive or dead. Every cell interacts with its eight neighbours, which are the cells that are horizontally, vertically, or diagonally adjacent to it. At each step in time, the following transitions occur:

- Any live cell with fewer than two live neighbours dies, as if caused by under-population.
- Any live cell with two or three live neighbours lives on to the next generation.
- Any live cell with more than three live neighbours dies, as if by overcrowding.
- Any dead cell with exactly three live neighbours becomes a live cell, as if by reproduction.

The initial pattern constitutes the seed of the system. The first generation is created by applying the above rules simultaneously to every cell in the seed—births and deaths occur simultaneously, and the discrete moment at which this happens is sometimes called a tick (in other words, each generation is a pure function of the preceding one)

## Usage
1. Download Docker from https://www.docker.com/products/docker-desktop
2. Clone the project: `git clone https://github.com/Xheradon/conway.git`
3. Execute `docker-compose up` in the project directory

## Usage without Docker
1. Download PHP >= 7.4 and Composer
2. Clone the project: `git clone https://github.com/Xheradon/conway.git`
3. Navigate to the project directory
4. Install docker dependencies `composer install` or `composer install --no-dev`
5. Run `php bin/console` to run the project

## Dependencies explanation
- symfony/console: for simplifying the execution of the game in a visual way
- phpunit/phpunit (dev): for running some tests

## Code explanation
- All game logic is inside src/Entity/Board.php