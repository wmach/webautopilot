<?php
/*****************************************************************************
 * Entities.php
 *     Name: Entities
 *     Date: 2008/12/16
 * @author makoto.warashina@gmail.com
 * @version $Id$:
 * @copyright &copy; 2008-2009 finatech inc.
 *****************************************************************************/
require_once 'OrderEntitiesVisitor.php';

/**
 * interface Entity: 実体、かつトラバースされる要素のインタフェース
 * @author  makoto.warashina@gmail.com
 * @access  public
 * @package Libs
 */
interface EntityIF{
public function getArray();
public function setArray($paramArray);
public function setThisClassArray($paramArray);
public function getThisClassArray();
public function getValue($paramKey);
public function setPair($paramKey,$paramValue);
public function accept($visitor);
}

/**
 * LoginEntity Entity: 実体の定義
 * @author  makoto.warashina@gmail.com
 * @access  public
 * @package Libs
 */
class LoginEntity implements EntityIF{
private $private_array = NULL;
public function __construct($paramArray){$this->private_array=$paramArray;}
public function setArray($paramArray){$this->private_array=$paramArray;}
public function getArray(){return $this->private_array;}
public function getThisClassArray(){
    return $this->private_array[get_class($this)];}
public function setThisClassArray($paramArray){
    $this->private_array[get_class($this)]=$paramArray;}
public function setPair($paramKey,$paramValue){
    $this->private_array[$paramKey]=$paramValue;}
public function getValue($paramKey){return $this->private_array[$paramKey];}
public function accept($visitor){return $visitor->visit($this);}
}

class TPositionEntity        extends LoginEntity{}
class ShinkiKaiEntryEntity   extends TPositionEntity{}
class ShinkiUriEntryEntity   extends TPositionEntity{}
class HensaiEntryEntity      extends TPositionEntity{}
class ShinkiKaiConfirmEntity extends ShinkiKaiEntryEntity{}
class ShinkiUriConfirmEntity extends ShinkiUriEntryEntity{}
class HensaiConfirmEntity    extends HensaiEntryEntity{}
class ShinkiKaiExEntity      extends ShinkiKaiConfirmEntity{}
class ShinkiUriExEntity      extends ShinkiUriConfirmEntity{}
class HensaiExEntity         extends HensaiConfirmEntity{}
class InsertTOrderEntity     extends TPositionEntity{}
class UpdateTPositionEntity  extends InsertTOrderEntity{}
class DetailViewEntity       extends UpdateTPositionEntity{}
?>
