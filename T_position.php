<?php
require_once 'Zend/Db/Table/Abstract.php';
class T_position extends Zend_Db_Table_Abstract
{
    protected $_name       = 't_position';
    protected $_primary    = 'pid';
    protected $_sequence   = 'pid';
    private $_program_name = NULL;

    public function __construct( $program_name ){
        $this->_program_name = $program_name;
        parent::__construct();
    }

    public function insert(array $data)
    {
        //タイムスタンプの追加
        if (empty($data['create_date'])) {
            $data['create_date'] = time();
        }
        if (empty($data['creator'])) {
            $data['creator'] = $this->_program_name;
        }
        return parent::insert($data);
    }

    public function update(array $data, $where)
    {
        //タイムスタンプの追加
        if (empty($data['update_date'])) {
//            $data['update_date'] = time();
        }
        if (empty($data['updater'])) {
            $data['updater'] = $this->_program_name;
        }
        return parent::update($data, $where);
    }
}
?>
