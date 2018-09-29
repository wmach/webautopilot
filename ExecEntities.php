<?php
/*****************************************************************************
 * ExecEntities.php
 *     Name: Execution related Entities
 *     Date: 2009/01/08
 * @author makoto.warashina@gmail.com
 * @version $Id$:
 * @copyright &copy; 2009 finatech inc.
 *****************************************************************************/
require_once 'Entities.php';

class YakujouKakuninEntity   extends DetailViewEntity{}
class InsertTExecutionEntity extends YakujouKakuninEntity{}
class TeiseiOrderEntity      extends YakujouKakuninEntity{}
class CalcelOrderEntity      extends YakujouKakuninEntity{}
?>
