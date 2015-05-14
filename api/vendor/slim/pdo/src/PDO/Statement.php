<?php
/**
 * @license MIT
 * @license http://opensource.org/licenses/MIT
 */
namespace Slim\PDO;

/**
 * Class Statement
 *
 * @package Slim-PDO
 * @author Fabian de Laender <fabian@faapz.nl>
 */
class Statement extends \PDOStatement
{
    /**
     * @var Database $dbh PDO object
     */
    protected $dbh;

    /**
     * Constructor
     *
     * @param Database $dbh PDO object
     */
    protected function __construct( Database $dbh )
    {
        $this->dbh = $dbh;
    }
}
