
| ファイル/ディレクトリ名称 | 内容 |ファイルの説明 |
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
