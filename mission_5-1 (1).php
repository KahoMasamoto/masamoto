<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
<body>

<?php
// データベースへの接続$dsn = 'mysql:dbname=tb230017db;host=localhost';
$dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
// データベース内にテーブルを作成
$sql = "CREATE TABLE IF NOT EXISTS pxbdata"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "time TIMESTAMP,"
    . "password char(32)"
    .");";
$stmt = $pdo->query($sql);

if(!empty($_POST["pw"])){//パスワード入力している場合
    $password=$_POST["pw"];//パスワードを取得
    //編集番号確認機能
    if(!empty($_POST["edit_number"])&& !empty($_POST["edit_message"])){//編集番号と編集の送信がある時
        $editnumber=$_POST["edit_number"];//編集番号を取得
        $id = $editnumber;
        $sql = 'SELECT * FROM pxbdata WHERE id=:id ';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            $readpassword=$row['password'];//元々のパスワードを取得
            if ($password == $readpassword){//パスワード認証
                $editnumber1=$row['id'];//編集番号を一時保存、78行と関連
                $editname=$row['name'];//
                $editcomment=$row['comment'];
            }else{
                echo "パスワードが間違っている。";
            }
        }
    }

    //削除機能
    if(!empty($_POST["delete_number"]) && !empty($_POST["delete_message"])){//削除番号と削除の送信がある時
        $deletenumber=$_POST["delete_number"];//削除番号を取得
        $id = $deletenumber;
        $sql = 'SELECT * FROM pxbdata WHERE id=:id ';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            $readpassword=$row['password'];//元々のパスワードを取得
            if ($password == $readpassword){//パスワード認証
                $sql = 'delete from pxbdata where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }else{
                echo "パスワードが間違っている。";
            }
        }
    }
}
?>

<form action="" method="post">
    
        <h1>「おいしかった、また行きたい」お店を記録しよう！</h1>
        <h2>……気に入ったメニューも記録していこう！……</h2>
        <input type="text" name="name" placeholder="お店の名前" value="<?php if(!empty($editnumber1)){echo $editname;}?>">~最寄り駅を記入してみてもいいかも！~<br>
        <input type="text" name="text" placeholder="お気に入りのメニュー" value="<?php if(!empty($editnumber1)){echo $editcomment;}?>">~理由も書いてみよう！~<br><br>
        <input type="hidden" name="h_editnumber"value="<?php if(!empty($editnumber1)){echo $_POST["edit_number"];}?>">
        <input type="password" name="pw" placeholder="パスワード" value="<?php if(!empty($editnumber1)){echo $readpassword;}?>">
        <input type="submit" name="send_message" value="送信">~パスワードと一致したら削除及び編集できます~<br><br>
        <input type="number" name="delete_number" placeholder="削除対象番号">
        <input type="submit" name="delete_message"value="削除">~削除もできます~<br><br>
        <input type="number" name="edit_number" placeholder="編集対象番号">
        <input type="submit" name="edit_message"value="編集">~2回目来店したら、更新しても良いかも！~<br>
    </fieldset>
</form>
<?php
//投稿
// 編集入力機能
if(!empty($_POST["send_message"])){//送信がある時
    if(!empty($_POST["name"])){//名前の入力がある時
        if(!empty($_POST["pw"])){//パスワードの入力がある時
            $name = trim($_POST['name']);//名前を取得
            $comment = trim($_POST['text']); //コメントを取得
            $password= trim($_POST["pw"]);//パスワードを取得
            $TIMESTAMP=new DateTime();//時間取得
            $TIMESTAMP=$TIMESTAMP->format("Y-m-d H:i:s");//時間の格式を決める
            // 編集機能
            if(!empty($_POST["h_editnumber"])){//編集番号がある時、「78行と関連している」
                $editnumber=$_POST["h_editnumber"];//編集番号を取得
                $id = $editnumber;
                $sql = 'UPDATE pxbdata SET name=:name,comment=:comment,time=:time, password=:password WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue('time',$TIMESTAMP, PDO::PARAM_STR);
                $stmt->execute();
                echo "$comment"." を更新しました。";
            }else{// 入力機能、編集番号がない時、新しいコメントを加える
                $sql = $pdo -> prepare("INSERT INTO pxbdata (name, comment, time, password) VALUES (:name, :comment, :time, :password)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindValue('time',$TIMESTAMP, PDO::PARAM_STR);
                $sql -> bindParam(':password', $password, PDO::PARAM_STR);
                $sql -> execute();
                echo "$comment"." を受け付けました。";
            }
        }else{//パスワードの入力していない場合、エラーを提示する
            echo "<font color='red'>"."!------------------------------------------!<br>パスワードを入力してください。<br>!------------------------------------------!<br>"."</font>";
        }
    }else{//名前の入力していない場合、エラーを提示する
        echo "<font color='red'>"."!---------------------------------!<br>名前を入力してください。<br>!---------------------------------!<br>"."</font>";
    }
}

echo "<br>以下は行ったお店の一覧です！：<hr>";

//表示機能
$sql = 'SELECT * FROM pxbdata';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
foreach ($results as $row){
    echo $row['id'].',';
    echo $row['name'].',';
    echo $row['comment'].',';
    echo $row['time'].'<br>';
    echo "<hr>";
}
?>
</body>
</html>