
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