<?php
/**
 * @license MIT
 * @license http://opensource.org/licenses/MIT
 */
namespace Slim\PDO;

/**
 * Class Database
 *
 * @package Slim-PDO
 * @author Fabian de Laender <fabian@faapz.nl>
 */
class Database extends \PDO
{
    /**
     * Constructor
     *
     * @param string      $dsn     Data Source Name
     * @param null|string $usr     DSN string username
     * @param null|string $pwd     DSN string password
     * @param array       $options Driver-specific connection options
     */
    public function __construct( $dsn , $usr = null , $pwd = null , array $options = array() )
    {
        $options = array(
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::ATTR_STATEMENT_CLASS    => array(
                'Slim\\PDO\\Statement',
                    array( $this )
            )
        ) + $options;

        @parent::__construct( $dsn , $usr , $pwd , $options );
    }
}
