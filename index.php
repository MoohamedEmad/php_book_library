<?php
session_start();

// Genres
$genres = ["Fiction", "Non-Fiction", "Science", "History", "Biography", "Technology"];

// Sample books
if (!isset($_SESSION['books']) || !is_array($_SESSION['books']) || count($_SESSION['books']) == 0)  {//     وبدخل trueلكن نعكس لتصبح booksلا تحتوي على    SESSION[''] في اول مرة تكون المصفوفة 
      $_SESSION['books'] = [

    ["id"=>1,"title"=>"Clean Code","author"=>"Robert Martin","genre"=>"Technology","year"=>2008,"pages"=>464],
    ["id"=>2,"title"=>"Atomic Habits","author"=>"James Clear","genre"=>"Non-Fiction","year"=>2018,"pages"=>320],
    ["id"=>3,"title"=>"1984","author"=>"George Orwell","genre"=>"Fiction","year"=>1949,"pages"=>328],
];}
$books=$_SESSION['books'];// books نضعه في المتغير 
$errors = [];//لنخزن الاخطاء
$submittedData = [// POST لنخزن القيم التي تاتي من ال 
    "title"=>"",
    "author"=>"",
    "genre"=>"",
    "year"=>"",
    "pages"=>""
];


$editMode = false;
$editId = null;
if(isset($_GET['edit_id'])){//   id ذا الرابط يحتوي على ال 
    $editId=(int)$_GET['edit_id'];// نضعه في المتغير 
    foreach($books as $book){// نلف على المصفوفة عنصر عنصر 
if($book['id']==$editId){// نقارن لنحصل على العنصر بناءا على الاي دي
$submittedData=[
"title"=>$book['title'],
    "author"=>$book['author'],
    "genre"=>$book['genre'],
    "year"=>$book['year'],
    "pages"=>$book['pages']
];
$editMode=true;
break;
}
}
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if(isset($_POST['delete_id'])){
        $deleteId =(int)$_POST['delete_id'];
        $_SESSION['books']=array_filter($_SESSION['books'],function($book)use($deleteId){// تسمح لنا باستخدام المتغير الي خارج الدالة :use تعني دالة بدون اسم و  :function 
            return $book['id']!= $deleteId;
        });
        $_SESSION['books'] = array_values($_SESSION['books']);// لاننا احتفظنا بلعناصر التي لا تسواي الاي دي المحذوف هنا نقوم بترتيب الاندكس
        $_SESSION["success"] = "Book deleted successfully!";//رسالة نجاح 
        header("Location: index.php");// نرسله لراس الصفحة حتى يقوم باعادة عرض العناصر بعد الحذف 
    exit;

    }
    foreach ($submittedData as $key => $value) {
       $submittedData[$key] = htmlspecialchars(trim(isset($_POST[$key])?$_POST[$key]: ""));// submittedData[ Key بدلالة ال ]نخزن القيم الجاية من البوست في المصفوفة
    }
    if (empty($submittedData["title"])) {
        $errors["title"] = "Title is required.";
    } elseif (strlen($submittedData["title"]) < 3 || strlen($submittedData["title"]) > 120) {
        $errors["title"] = "Title must be between 3 and 120 characters.";
    }
    if (empty($submittedData["author"])) {
        $errors["author"] = "Author is required.";
    } elseif (str_word_count($submittedData["author"]) < 2) {
        $errors["author"] = "Author must contain at least two words.";
    }
    if (empty($submittedData["genre"]) || !in_array($submittedData["genre"], $genres)) {
        $errors["genre"] = "Invalid genre.";
    }
    $currentYear = date("Y");
    if (!preg_match("/^\d{4}$/", $submittedData["year"]) ||
        $submittedData["year"] < 1000 ||
        $submittedData["year"] > $currentYear) {
        $errors["year"] = "Invalid year.";
    }
    if (!filter_var($submittedData["pages"], FILTER_VALIDATE_INT) || $submittedData["pages"] <= 0) {
        $errors["pages"] = "Pages must be a positive number.";
    }
    if (empty($errors)) {
       
      if(isset($_POST['edit_id'])&&!empty($_POST['edit_id'])){
        $updatid=(int)$_POST['edit_id'];
        foreach($_SESSION['books']as $index=>$book){
            if($book['id']==$updatid){
                $_SESSION['books'][$index]=[
                "id" => $updatid,
                "title" => $submittedData["title"],
                "author" => $submittedData["author"],
                "genre" => $submittedData["genre"],
                "year" => $submittedData["year"],
                "pages" => $submittedData["pages"]
                ];
                break;
            }
        }
$_SESSION["success"]="book is update ";
header("Location: index.php");
exit;

      }else{
         $maxId = max(array_column($_SESSION['books'], "id"));
        $newId = $maxId + 1;
       $_SESSION['books'][] = [
            "id"=>$newId,
            "title"=>$submittedData["title"],
            "author"=>$submittedData["author"],
            "genre"=>$submittedData["genre"],
            "year"=>$submittedData["year"],
            "pages"=>$submittedData["pages"]
        ];        
        $_SESSION["success"] = "Book added successfully!";
        header("Location: index.php");
        exit;
    }
}
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Library</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>

<body>

<div class="container mt-5">
<div class="row"> <!-- ننشئ صف-->
<div class="col-md-5"><!-- عمود الاول داخل الصف -->

    <h3><?php echo $editMode ?'Edit Book':'Add Book'?></h3>

    <!-- اذا قيمة ال session["success"] -->
    <?php if(isset($_SESSION["success"])): ?>
        <div class="alert alert-success alert-dismissible "><!-- يضهر تنبيه ولونه اخضر مع مساحة لاضافة الاكس -->
        <?php echo $_SESSION["success"]; 
            unset($_SESSION["success"]); ?> <!-- نطبع جملة الطباعة المخزنةفي المصمفوة ثم حذف القيمة حتى لا تظهر الرسالة في كل مرة يحدث الصفحة  -->

          <button class="btn-close" data-bs-dismiss="alert"></button><!--div = alert الكلاس ///ثم القسم الثاني يقوم باغلاق ال btn-close يظهر شكل الاكس   -->
        </div>
    <?php endif; ?>

    <?php if(!empty($errors)): ?>  <!-- اذا كانت مصفوفة الاخطاء غير فارغة اضهر اشعار  -->
        <div class="alert alert-danger">Please fix the errors.</div>
    <?php endif; ?>

    <form method="POST">
<?php if ($editMode): ?>
    <input type="hidden" name="edit_id" value="<?php echo $editId ?>">
<?php endif; ?>



        
        <div class="mb-3"><!-- margin buttom =3 -->
            <label class="form-label">Title</label>
            <input name="title" class="form-control <?php echo isset($errors['title'])?'is-invalid':'';?>"
            value ="<?php echo isset($submittedData['title'])? htmlspecialchars($submittedData['title']):'';?>"><!-- وعند اعادة تحميل الصفحة يعيد القيمة السابقة في الحقل 
           form-control مع  is-invalid اذا يوجد خطا اجعل الحقل به خاصية  -->
            <div class="invalid-feedback"><?php echo isset($errors['title'] )? $errors['title']:''; ?></div><!--php يطبع رسالة الخطا الي في --> 
        </div>




        <div class="mb-3">
            <label class="form-label">Author</label>
            <input name="author" class="form-control <?php echo isset($errors['author'])?'is-invalid':'';?>" value="<?php echo isset($submittedData['author'])? htmlspecialchars($submittedData['author']):'';?>"> 
            <div class="invalid-feedback"><?php echo isset($errors['author'])?$errors['author']:'';  ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Genre</label>
            <select name="genre" class="form-control <?php echo isset($errors['genre'])?'is-invalid':'' ?>">
                <option value="">Select</option>
                <?php foreach($genres as $g): ?>
                    <option value="<?php echo $g ?>" <?php echo $submittedData['genre']==$g?'selected':'' ?>><!--    يحددها  genre اذا كانت القيمة المختارة سابقا تساوي القيمة الموجودة حاليا في مصفوفة ال   -->
                        <?= $g ?> <!--في كل لفة يصنع خيار داخل قيمة من المصفوفة -->
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?php echo isset($errors['genre'])?$errors['genre'] : '' ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Year</label>
            <input name="year" class="form-control <?php echo isset($errors['year'])?'is-invalid':'' ?>"
                        value="<?php echo isset($submittedData['year']) ? htmlspecialchars($submittedData['year']) : ''; ?>">
            <div class="invalid-feedback"><?php echo isset($errors['year'])?$errors['year']:'' ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Pages</label>
            <input name="pages" class="form-control <?php echo isset($errors['pages'])?'is-invalid':'' ?>"
                    value="<?php echo isset($submittedData['pages']) ? htmlspecialchars($submittedData['pages']) : ''; ?>">
            <div class="invalid-feedback"><?php echo isset($errors['pages'])?$errors['pages']: '' ?></div>
        </div>

        
<button class="btn btn-primary"><?php echo $editMode ? 'Update Book' : 'Add Book' ?></button>
<?php if ($editMode): ?>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
<?php endif; ?>

    </form>

</div>
<div class="col-md-7">

    <h3>Books</h3>

    <table class="table table-striped table-hover table-bordered">
        <thead>
            <tr>
                <th>Actions</th>
                <th>#</th>
                <th>Title</th>
                <th>Author</th>
                <th>Genre</th>
                <th>Year</th>
                <th>Pages</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($books as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['id']) ?></td>
                <td><?= htmlspecialchars($b['title']) ?></td>
                <td><?= htmlspecialchars($b['author']) ?></td>
                <td><?= htmlspecialchars($b['genre']) ?></td>
                <td><?= htmlspecialchars($b['year']) ?></td>
                <td><?= htmlspecialchars($b['pages']) ?></td>
                <td>
    <a href="?edit_id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">Edit</a><!-- deit_id= id the curent bookنرسل قيمة المتغير  -->
    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= $b['id'] ?>">Delete</button>
</td>
            </tr>
        <?php endforeach; ?>


<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true"><!--صندوق منبثق -->
    <div class="modal-dialog">
        <div class="modal-content"><!--جسم الصندوق-->
            <div class="modal-header"><!--راس-->
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5><!--العنوان-->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this book?
            </div>
            <div class="modal-footer">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_id" id="delete_id" value=""><!--نخزن الايد دي تاع الكتاب الذي ضغطنا حذف عليه عشان نمسكه من البوست-->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>








        </tbody>
    </table>

</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;      // الزر الذي فتح المودال
        const bookId = button.getAttribute('data-id');
        const hiddenInput = document.getElementById('delete_id');
        hiddenInput.value = bookId;
    });

</script>

</body>
</html>