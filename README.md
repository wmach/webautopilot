# SBI証券ウェブサイトをスクレイピングして自動発注を行うシステム
SBI証券の現物株の信用取引のウェブサイトの入力フォームから自動で入力して発注します。
- 発注時の画面遷移を１つのクラス、Visitorクラスにまとめました。
- 入力フォームの入力データフローをコンポジットパターンで構造化しました。
- クラスの継承木をcompositionに見立てて画面遷移を表現しました。
- visitorパターンのvisitor役クラス内メソッドを再帰的に呼出します。

## クラスの継承木をコンポジット的に定義する
```
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
```
## 再帰的なメソッド呼び出し
ビジネスロジッククラスのメソッドを再帰的に呼出すと、次のような継承木をトラバースする単純なメインループになる。
```
/**
 * orderMainLoop: ビジネスロジック遂行処理
 * @param: $entity: トークン配列（解析済みリクエスト）
 *         $visitor: visitorパターンのvisitor役
 */
public function orderMainLoop( $entity, $visitor )　{
  // 発注画面が終わり、
  // T_POSITION と T_ORDER の両方に登録するまでループ
  while (!($entity instanceof TExecutionEntity)){
      //再帰的にaccept()関数を呼び出す
      $entity = $entity->accept( $visitor );
  }
  return $entity;
}
```
約定のVisitorクラスのvisitメソッドでデータオブジェクトを解析して、処理を振り分ける。
```
/**
 * visit: 渡された実体から数珠繋ぎになっている実体を返す
 * @param: $paramEntity: 実体(要素element)
 * @return: entity: 実体(要素element)
 */
public function visit( $paramEntity ) {
  if ( $paramEntity instanceof DetailViewEntity){
    return $this->detailView( $paramEntity );}
  elseif ( $paramEntity instanceof OrderDoneEntity ){
    $entity = $this->httpLogin( $paramEntity );
    return new DetailViewEntity($entity->getArray());
  }
}
```
## 継承木を使った再帰的メソッド呼出のクラス図
![継承木コンポジット](https://github.com/wmach/webautopilot/blob/master/tree.png)

## 使い方
クラウド上のウェブサーバーURLにアクセスして発注を行います。
```
'pid': ポジションID
'gid'
'code': 銘柄コード
'ls': 売買区分
'entry_exit': 取引区分
'price': 価格
'qty': 数量
```
〔 銘柄コード4346を、1,600円で100単位、新規で売りの場合のURL例 〕
http://トレードエージェントのアクセスURL/TradeAgent?pid=1&gid=1&code=4346&ls=-1&entry_exit=&price=1600&qty=100

| ファイル/ディレクトリ名称 | 内容 |ファイルの説明 |
|-|-|-|
| AbstractVisitor.php | 抽象クラス定義 | Visitorパターンのvisitor役クラスの親クラス |
| CodesConstants.php | 定数クラス定義 | 定数値格納クラス、コード表 |
| Entities.php | インタフェース / クラス 定義 | ページ間でデータの受け渡し用に使用するインタフェース（EntityIF）の定義とその実装クラス（注文ページ用）の定義 |
| ExecEntities.php | クラス定義 | 約定確認ページ用のEntityIFの実装クラス定義 |
| ExecEntitiesVisitor.php | クラス定義 | 約定確認用のAbstractVisitorの実装クラス 約定の確認＋訂正発注（'指値'→'成行'のみ）＋取消発注 |
| Login.php | クラス定義 | Zendログイン用 |
| CurlLogin.php | クラス定義 | Curlログイン用 |
| Http.php | クラス定義 | Httpクラスに関数を追加して wrap したもの |
| OrderEntitiesVisitor.php | クラス定義 | 発注用のAbstractVisitorの実装クラス 最初の発注（'指値'のみ） |
| SBIConstants.php | 定数クラス定義 | SBIバックアップサイト用定数値格納クラス。各種URL、スクレイピング用正規表現など |
| SBIMessages.php | 定数クラス定義 | メッセージ定数値格納クラス |
| T_execution.php | クラス定義 | T_EXECUTIONテーブル（約定トランザクション）のDAO |
| T_order.php | クラス定義 | T_ORDERテーブル（注文トランザクション）のDAO |
| T_position.php | クラス定義 | T_POSITIONテーブル（ポジション管理トランザクション）のDAO |
| TradeAgent.php | クラス定義 | エントリポイント。メインループ |
| ZendConstants.php | 定数クラス定義 | Zend F/W ライブラリ用定数値格納クラス |
| tradeagent.xml | XML設定ファイル |  |
| Zend/ | ＊ディレクトリ | Zend F/W ライブラリ群格納ディレクトリ |

【ファイル名命名規約】
定数クラスは、サフィックスに Constants
【クラス名命名規約】
抽象クラス： プリフィックスに Abstract
インタフェース： サフィックスに IF
その他： デザインパターンを使っている場合はその役柄をサフィックス

```tradeagent.xml
<?xml version="1.0" ?>	
<tradeagent>	
  <database>	……………… データベース設定
    <adapter>Pdo_Pgsql</adapter>	
    <host>192.168.11.199</host>	
    <username>XXXXXXXX</username>	
    <password>XXXXXXXX</password>	
    <dbname>realtrade</dbname>	
  </database>	
  <execution>	……………… 約定確認（約定確認回数 × 約定確認間隔）秒の間、約定を確認します。
    <list>scenario1,scenario2,scenario3</list>	……………… 繰り返し項目を枚挙（※カンマで区切る、スペース等は受け付けない）
    <scenario1>	
      <condition>sasi</condition>	……………… 執行条件'指値'１番目のシナリオの執行条件指定は無視され、常に'指値'となる
      <waitingcount>20</waitingcount>	……………… 約定確認回数
      <waitinginterval>30</waitinginterval>	……………… 約定確認間隔（単位：秒）
    </scenario1>	
    <scenario2>	
      <condition>nari</condition>	……………… 執行条件'成行'
      <waitingcount>10</waitingcount>	……………… 約定確認回数
      <waitinginterval>15</waitinginterval>	……………… 約定確認間隔（単位：秒）
    </scenario2>	
    <scenario3>	
      <condition>tori</condition>	……………… 執行条件'取消'（※取消注文の発注）
      <waitingcount>3</waitingcount>	……………… ※執行条件が'取消'のときは無視され、取消注文発注後直ちにプログラム終了
      <waitinginterval>2</waitinginterval>	……………… ※執行条件が'取消'のときは無視され、取消注文発注後直ちにプログラム終了
    </scenario3>	
  </execution>	
  <sbibackup>	……………… 接続先サイト情報設定
    <account>	
      <loginuser>********</loginuser>	
      <loginpass>********</loginpass>	
      <tradepass>********</tradepass>	
    </account>	
  </sbibackup>	
</tradeagent>	
```