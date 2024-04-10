<?php
//define ('FILENAME','./message.txt');

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

//変数の初期化
$current_date = null;
//$data = null;
//$file_handle = null;
//入力データ一行分を格納し分割するための変数
//$split_data = null; 
//分割されたデータを配列として格納する
$message = array();
//配列messageを要素として取り扱うための配列(二次元配列のイメージ)
$message_array = array();

$success_message = null;
$error_message = array();
//$clean = array();

//データベースへのアクセスに使う変数たち
$pdo = null;
$stmt = null;
$res = null;
$option = null;

// データベースに接続
try{
    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
    );
    $pdo = new PDO('mysql:charset=UTF8;dbname=board;host=localhost', 'root', '',$option);
} catch(PDOException $e){
    //接続エラーのときエラー内容を取得する
    $error_message[] = $e->getMessage();
}

if (!empty($_POST['btn_submit'])){
    //var_dump($_POST);

    // 空白除去
    //文字列の先頭に連なる空白改行等の除去と、文末に連なる空白改行等の除去
    $view_name = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['view_name']);
    $message = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['message']);
    // 表示名の入力チェック
    if(empty($view_name)){
        $error_message[] = '表示名を入力してください。';
    }
    /*
    else {
        echo '$_POST view name from here';
        var_dump($_POST['view_name']);
        echo '$_POST view name end here';
        
        $clean['view_name'] = htmlspecialchars($_POST['view_name'],ENT_QUOTES,'UTF-8');
        $clean['view_name'] = preg_replace('/\\r\\n|\\n|\\r/','',$clean['view_name']);
        echo 'clean view name from here</br>';
        var_dump($clean['view_name']);
        echo 'clean view name end here</br>';
    }*/


    // メセージの入力チェック
    if( empty($message)){
        $error_message[] = 'ひと言メッセージを入力してください。';
    }
    /*
    else {
        $clean['message'] = htmlspecialchars($_POST['message'],ENT_QUOTES,'UTF-8');
        $clean['message'] = preg_replace( '/\\r\\n|\\n|\\r/', '<br>', $clean['message']);
    }
    */

    if(empty($error_message)){
        /* ファイルへの書き込みをコメントアウト
        if( $file_handle = fopen( FILENAME, "a")){
            $current_date = date("Y-m-d H:i:s");
            //書き込むデータを作成
            $data = "'".$clean['view_name']."','".$clean['message']."','".$current_date."'\n";

            //  書き込み
            fwrite( $file_handle, $data);
        //ファイルを閉じる
            fclose( $file_handle);

            $success_message = 'メッセージを書き込みました';
        }
        */

        /* 書き込み日時を取得 */
        $current_date = date("Y-m-d H:i:s");

        //トランザクション開始
        $pdo->beginTransaction();
        try{
            //SQL作成
            $stmt = $pdo->prepare("INSERT INTO message (view_name, message, post_date)
            VALUES ( :view_name, :message, :current_date)");

            //値をセット PDO::PARAM_STR クラス内の定数？を指定している　文字列
            $stmt->bindParam( ':view_name', $view_name, PDO::PARAM_STR);
		    $stmt->bindParam( ':message', $message, PDO::PARAM_STR);
		    $stmt->bindParam( ':current_date', $current_date, PDO::PARAM_STR);

            // SQLクエリの実行 
            //$res = $stmt->execute();
            $stmt->execute();
            //コミット
            $res = $pdo->commit();
        }catch (Exception $e){
            //エラーが発生したときはロールバック
            $pdo->rollBack();
        }

        if( $res ){
            $success_message = 'メッセージを書き込みました。';            
        } else {
            $error_message[] = '書き込みに失敗しました。';
        }

        //プリペアドステートメントを削除
        $stmt = null;
    }
}


if( empty($error_message) ) {

	// メッセージのデータを取得する
	$sql = "SELECT view_name,message,post_date FROM message ORDER BY post_date DESC";
	$message_array = $pdo->query($sql);

    var_dump($message_array);
}
//データベース接続を閉じる
$pdo = null;

/* ファイルの読み込みはコメントアウトする
if($file_handle = fopen(FILENAME,'r')){
    //ファイルから一行ずつ読み込む
    while($data = fgets($file_handle)){
        echo $data."<br>";
        //正規表現を用いて、'で文字列を分割している
        $split_data = preg_split('/\'/',$data);

        //配列の宣言 view_name等がkeyとなる
        $message = array(
            'view_name' => $split_data[1],
            'message' => $split_data[3],
            'post_date' => $split_data[5]
        );
        //配列の先頭に要素を加える 添字が数字だったとしたら、0番目に要素が加わり
        //0番目が1番目になりn番目がn+1番目になる
        array_unshift($message_array,$message);
        //var_dump($message_array);
    }
    //ファイルを閉じる
    fclose($file_handle);
}
*/
?> 

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>ひと言掲示板</title>
<style>

/*------------------------------

 Reset Style
 
------------------------------*/
html, body, div, span, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
abbr, address, cite, code,
del, dfn, em, img, ins, kbd, q, samp,
small, strong, sub, sup, var,
b, i,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, figcaption, figure,
footer, header, hgroup, menu, nav, section, summary,
time, mark, audio, video {
    margin:0;
    padding:0;
    border:0;
    outline:0;
    font-size:100%;
    vertical-align:baseline;
    background:transparent;
}

body {
    line-height:1;
}

article,aside,details,figcaption,figure,
footer,header,hgroup,menu,nav,section {
    display:block;
}

nav ul {
    list-style:none;
}

blockquote, q {
    quotes:none;
}

blockquote:before, blockquote:after,
q:before, q:after {
    content:'';
    content:none;
}

a {
    margin:0;
    padding:0;
    font-size:100%;
    vertical-align:baseline;
    background:transparent;
}

/* change colours to suit your needs */
ins {
    background-color:#ff9;
    color:#000;
    text-decoration:none;
}

/* change colours to suit your needs */
mark {
    background-color:#ff9;
    color:#000;
    font-style:italic;
    font-weight:bold;
}

del {
    text-decoration: line-through;
}

abbr[title], dfn[title] {
    border-bottom:1px dotted;
    cursor:help;
}

table {
    border-collapse:collapse;
    border-spacing:0;
}

hr {
    display:block;
    height:1px;
    border:0;
    border-top:1px solid #cccccc;
    margin:1em 0;
    padding:0;
}

input, select {
    vertical-align:middle;
}

/*------------------------------

Common Style

------------------------------*/
body {
	padding: 50px;
	font-size: 100%;
	font-family:'ヒラギノ角ゴ Pro W3','Hiragino Kaku Gothic Pro','メイリオ',Meiryo,'ＭＳ Ｐゴシック',sans-serif;
	color: #222;
	background: #f7f7f7;
}

a {
    color: #007edf;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

h1 {
	margin-bottom: 30px;
    font-size: 100%;
    color: #222;
    text-align: center;
}


/*-----------------------------------
入力エリア
-----------------------------------*/

label {
    display: block;
    margin-bottom: 7px;
    font-size: 86%;
}

input[type="text"],
textarea {
	margin-bottom: 20px;
	padding: 10px;
	font-size: 86%;
    border: 1px solid #ddd;
    border-radius: 3px;
    background: #fff;
}

input[type="text"] {
	width: 200px;
}
textarea {
	width: 50%;
	max-width: 50%;
	height: 70px;
}
input[type="submit"] {
	appearance: none;
    -webkit-appearance: none;
    padding: 10px 20px;
    color: #fff;
    font-size: 86%;
    line-height: 1.0em;
    cursor: pointer;
    border: none;
    border-radius: 5px;
    background-color: #37a1e5;
}
input[type=submit]:hover,
button:hover {
    background-color: #2392d8;
}

hr {
	margin: 20px 0;
	padding: 0;
}

.success_message {
    margin-bottom: 20px;
    padding: 10px;
    color: #48b400;
    border-radius: 10px;
    border: 1px solid #4dc100;
}

.error_message {
    margin-bottom: 20px;
    padding: 10px;
    color: #ef072d;
    list-style-type: none;
    border-radius: 10px;
    border: 1px solid #ff5f79;
}

.success_message,
.error_message li {
    font-size: 86%;
    line-height: 1.6em;
}


/*-----------------------------------
掲示板エリア
-----------------------------------*/

article {
	margin-top: 20px;
	padding: 20px;
	border-radius: 10px;
	background: #fff;
}
article.reply {
    position: relative;
    margin-top: 15px;
    margin-left: 30px;
}
article.reply::before {
    position: absolute;
    top: -10px;
    left: 20px;
    display: block;
    content: "";
    border-top: none;
    border-left: 7px solid #f7f7f7;
    border-right: 7px solid #f7f7f7;
    border-bottom: 10px solid #fff;
}
	.info {
		margin-bottom: 10px;
	}
	.info h2 {
		display: inline-block;
		margin-right: 10px;
		color: #222;
		line-height: 1.6em;
		font-size: 86%;
	}
	.info time {
		color: #999;
		line-height: 1.6em;
		font-size: 72%;
	}
    article p {
        color: #555;
        font-size: 86%;
        line-height: 1.6em;
    }

@media only screen and (max-width: 1000px) {

    body {
        padding: 30px 5%;
    }

    input[type="text"] {
        width: 100%;
    }
    textarea {
        width: 100%;
        max-width: 100%;
        height: 70px;
    }
}
</style>
</head>
<body>
<h1>ひと言掲示板</h1>
<!-- 書き込み成功のメッセージを表示する -->
<?php if( !empty($success_message)): ?>
    <!-- $success_messageがから(null)出ないときに以下を表示する -->
    <p class="success_message"><?php echo $success_message; ?></p>
<?php endif; ?>
<?php if(!empty($error_message)): ?>
    <ul class="error_message">
        <?php foreach($error_message as $value):?>
            <li><?php echo $value; ?></li>
            <?php endforeach; ?>
    </ul>
 <?php endif; ?>
<!-- ここにメッセージの入力フォームを設置 -->
<form method="post">
    <div>
        <label for="view_name">表示名</lavel>
        <input id="view_name" type="text" name="view_name" value="">
    </div>
    <div>
        <label for="message">ひと言メッセージ</label>
        <textarea id="message" name="message"></textarea>
    </div>
    <input type="submit" name="btn_submit" value="書き込む">
</form>
<hr>
<section>
<!-- ここに投稿されたメッセージを表示 -->
<!-- コメントアウト　ここから -->
<!-- ここまで -->
<!-- 上のコメントと同じ意味のことを別の表現で書いてある -->
    <?php
        //message_arrayが空(null)でないとき以下のコードが実行される
        if(!empty($message_array)){
            //message_arrayの各要素に対して、以下のコードを実行する
            foreach($message_array as $value){?>

                <article>
                    <div class="info">
                        <h2><?php echo $value['view_name']; ?></h2>
                        <time><?php echo date('Y年m月d日H:i',strtotime($value['post_date'])); ?></time>
                    </div>
                    <p><?php echo nl2br($value['message']); ?></p>
                </article>
                <?php
            }
        }
    ?>
</section>
</body>
</html>