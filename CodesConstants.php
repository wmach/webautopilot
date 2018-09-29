<?php
class CodesConstants
{
    //注文系コード表
    const ORD_COND_SASHINE    = 1;
    const ORD_COND_NARIYUKI   = 2;
    const ORD_COND_YORITSUKI  = 3;
    const ORD_COND_HIKE       = 4;
    const ORD_COND_GYAKUSASHI = 5;

    const ORD_LS_SHINKI_KAI   = 1;
    const ORD_LS_SHINKI_URI   = -1;

    const ORD_ENTEXT_SHINKI   = 1;
    const ORD_ENTEXT_HENSAI   = -1;

    const ORD_CLS_TSUJOU      = 1;
    const ORD_CLS_TEISEI      = 2;
    const ORD_CLS_KYOHI       = 3;
    const ORD_CLS_TORIKESHI   = 4;

    const MUJOUKEN_SASHI      = ' ';
    const YORI_SASHI          = 'Z';
    const HIKE_SASHI          = 'I';
    const FUNARI              = 'F';
    const MUJOUKEN_NARIYUKI   = 'N';
    const YORI_NARI           = 'Y';
    const HIKE_NARI           = 'H';

    const T_POSITION_PID      = 'pid';        //ポジションID
    const T_POSITION_GID      = 'gid';
    const T_POSITION_CODE     = 'code';       //銘柄コード
    const T_POSITION_LS       = 'ls';         //売買区分
    const T_POSITION_ENTRY_EXIT = 'entry_exit';//取引区分
    const T_POSITION_PRICE    = 'price';      //価格
    const T_POSITION_QTY      = 'qty';        //数量

    const T_ORDER_ORDER_NO    = 'order_no';
    const T_ORDER_ORDER_BRNO  = 'order_brno'; //2.注文枝番号
    const T_ORDER_GID         = 'gid';        //3.グループＩＤ
    const T_ORDER_CODE        = 'code';       //4.銘柄コード
    const T_ORDER_YMD         = 'ymd';        //5.発注年月日
    const T_ORDER_HIS         = 'his';        //6.発注時分秒
    const T_ORDER_CONDITION   = 'condition';  //7.執行条件
    const T_ORDER_LS          = 'ls';         //8.売買区分
    const T_ORDER_ENTRY_EXIT  = 'entry_exit'; //9.取引区分
    const T_ORDER_PRICE       = 'price';      //10.イベント価格
    const T_ORDER_QTY         = 'qty';        //11.イベント数量
    const T_ORDER_ORDER_CLS   = 'order_cls';  //12.注文区分
    const T_ORDER_RETURN_ORDER_NO = 'return_order_no';//13.付合せ注文番号
    const T_ORDER_TOTAL_QTY   = 'total_qty';  //14.総約定数量
    const T_ORDER_TOTAL_PRICE = 'total_price';//15.総約定価格
    const T_ORDER_EXEC_CLS    = 'exec_cls';   //16.約定区分
}
?>
