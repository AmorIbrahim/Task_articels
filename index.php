<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "articels_dash";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

function flash($type, $msg) {
    $_SESSION['flash'] = ['type'=>$type, 'msg'=>$msg];
}


if (isset($_POST['add_user'])) {
    $name   = $_POST['name'] ?? '';
    $email  = $_POST['email'] ?? '';
    $role   = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';

    $stmt = $conn->prepare("INSERT INTO users (name,email,password,role,status,created_at,updated_at) VALUES (?,?,?,?,?,NOW(),NOW())");
    $default_pass = '123456';
    $stmt->bind_param("sssss", $name, $email, $default_pass, $role, $status);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم إضافة المستخدم بنجاح.');
    header("Location: " . $_SERVER['PHP_SELF'] . "#users");
    exit;
}

if (isset($_POST['edit_user'])) {
    $id     = intval($_POST['id']);
    $name   = $_POST['name'] ?? '';
    $email  = $_POST['email'] ?? '';
    $role   = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssssi", $name, $email, $role, $status, $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم تحديث بيانات المستخدم.');
    header("Location: " . $_SERVER['PHP_SELF'] . "#users");
    exit;
}

if (isset($_POST['delete_user'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم حذف المستخدم.');
    header("Location: " . $_SERVER['PHP_SELF'] . "#users");
    exit;
}

if (isset($_POST['add_article'])) {
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $user_id = intval($_POST['user_id']);
    $status  = $_POST['status'] ?? 'draft';

    $stmt = $conn->prepare("INSERT INTO articles (user_id,title,content,status,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())");
    $stmt->bind_param("isss", $user_id, $title, $content, $status);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم إضافة المقال.');
    header("Location: " . $_SERVER['PHP_SELF'] . "#articles");
    exit;
}

if (isset($_POST['edit_article'])) {
    $id      = intval($_POST['id']);
    $title   = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $status  = $_POST['status'] ?? 'draft';

    $stmt = $conn->prepare("UPDATE articles SET title=?, content=?, status=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sssi", $title, $content, $status, $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم تحديث المقال.');
    header("Location: " . $_SERVER['PHP_SELF'] . "#articles");
    exit;
}

// حذف مقال
if (isset($_POST['delete_article'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM articles WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'تم حذف المقال.');
    header("Location: " . $_SERVER['PHP_SELF'] . "#articles");
    exit;
}

function getSort($prefix, $default_col, $default_dir = "ASC") {
    $col = $_GET[$prefix . "_sort"] ?? $default_col;
    $dir = strtoupper($_GET[$prefix . "_dir"] ?? $default_dir);
    return [$col, ($dir === "DESC") ? "DESC" : "ASC"];
}
function sortLink($prefix, $col, $title, $current_col, $current_dir) {
    $dir = ($current_col == $col && $current_dir == "ASC") ? "DESC" : "ASC";
    $query = $_GET;
    $query[$prefix . "_sort"] = $col;
    $query[$prefix . "_dir"]  = $dir;
    $url = "?" . http_build_query($query);
    $arrow = ($current_col == $col) ? ($current_dir == "ASC" ? "▲" : "▼") : "";
    return "<a href='$url'>$title $arrow</a>";
}
function renderPagination($prefix, $page, $total_pages) {
    if ($total_pages <= 1) return;
    echo '<nav><ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $page) ? "active" : "";
        $query = $_GET;
        $query[$prefix] = $i;
        $url = "?" . http_build_query($query);
        echo "<li class='page-item $active'><a class='page-link' href='$url'>$i</a></li>";
    }
    echo '</ul></nav>';
}

$users_for_select = [];
$res_users_select = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
while ($r = $res_users_select->fetch_assoc()) $users_for_select[] = $r;

list($user_sort_col, $user_sort_dir) = getSort("user", "id");
$user_page     = isset($_GET['user_page']) ? max(1, intval($_GET['user_page'])) : 1;
$user_limit    = 5;
$user_offset   = ($user_page - 1) * $user_limit;
$user_search   = $_GET['user_search'] ?? "";
$user_where    = $user_search ? "WHERE name LIKE '%".$conn->real_escape_string($user_search)."%' OR email LIKE '%".$conn->real_escape_string($user_search)."%' " : "";
$user_sql      = "SELECT * FROM users $user_where ORDER BY $user_sort_col $user_sort_dir LIMIT $user_limit OFFSET $user_offset";
$users_result  = $conn->query($user_sql);
$user_total    = $conn->query("SELECT COUNT(*) as c FROM users $user_where")->fetch_assoc()['c'];
$user_total_pages = ceil($user_total / $user_limit);

list($article_sort_col, $article_sort_dir) = getSort("article", "id");
$article_page   = isset($_GET['article_page']) ? max(1, intval($_GET['article_page'])) : 1;
$article_limit  = 5;
$article_offset = ($article_page - 1) * $article_limit;
$article_search = $_GET['article_search'] ?? "";
$article_where  = $article_search ? "WHERE a.title LIKE '%".$conn->real_escape_string($article_search)."%' OR u.name LIKE '%".$conn->real_escape_string($article_search)."%' " : "";
$article_sql = "
    SELECT a.id, a.title, a.content, a.status, u.name AS author, a.user_id
    FROM articles a
    JOIN users u ON a.user_id = u.id
    $article_where
    ORDER BY $article_sort_col $article_sort_dir
    LIMIT $article_limit OFFSET $article_offset
";
$articles_result = $conn->query($article_sql);
$article_total   = $conn->query("SELECT COUNT(*) as c FROM articles a JOIN users u ON a.user_id = u.id $article_where")->fetch_assoc()['c'];
$article_total_pages = ceil($article_total / $article_limit);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="utf-8">
  <title>لوحة التحكم</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: "Tajawal", sans-serif; }
    .sidebar { height: 100vh; background: #212529; color: white; padding: 20px; position: fixed; top: 0; left: 0; width: 220px; transition: margin 0.3s; z-index:1050; }
    .sidebar h4 { color: #ffc107; }
    .sidebar a { display: block; color: #adb5bd; text-decoration: none; padding: 10px; margin: 5px 0; border-radius: 8px; }
    .sidebar a.active, .sidebar a:hover { background: #343a40; color: white; }
    .content { margin-left: 240px; padding: 30px; transition: margin 0.3s; }
    .card { box-shadow: 0 4px 8px rgba(0,0,0,0.06); border-radius: 12px; }
    .table th a { color: white; text-decoration: none; }
    .pagination .page-link { color: #212529; }
    .pagination .active .page-link { background: #ffc107; border-color: #ffc107; }
    .toggle-btn { position: fixed; top: 15px; left: 15px; z-index: 1100; border-radius: 50%; width: 45px; height: 45px; font-size: 20px; }
    .sidebar.collapsed { margin-left: -240px; }
    .content.collapsed { margin-left: 20px; }
    .btn-sm { padding: .25rem .4rem; font-size: .78rem; }
    .modal-body textarea { min-height: 120px; }
    .alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 2000; min-width: 260px; }
  </style>
</head>
<body dir="rtl">

<button class="btn btn-dark toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="sidebar">
  <h4 class="mb-4"> Dashboard</h4>
  <ul class="nav flex-column nav-pills" id="sideTabs">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#users"> المستخدمين</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#articles"> المقالات</a>
    </li>
  </ul>
</div>

<?php if (!empty($_SESSION['flash'])): ?>
  <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-fixed" role="alert">
    <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
  </div>
  <?php unset($_SESSION['flash']); ?>
  <script>
    setTimeout(()=>{ document.querySelector('.alert-fixed')?.remove(); }, 3000);
  </script>
<?php endif; ?>

<div class="content">
  <div class="tab-content">

    <div class="tab-pane fade show active" id="users">
      <div class="card p-4 mb-4">
        <h4> جدول المستخدمين</h4>
        <button class="btn btn-success mb-3" data-bs-toggle="collapse" data-bs-target="#addUserForm"> إضافة مستخدم جديد</button>
        <div id="addUserForm" class="collapse mb-3">
          <form method="POST" class="card card-body">
            <input type="text" name="name" placeholder="الاسم" class="form-control mb-2" required>
            <input type="email" name="email" placeholder="الإيميل" class="form-control mb-2" required>
            <select name="role" class="form-control mb-2">
              <option value="user">User</option>
              <option value="editor">Editor</option>
              <option value="admin">Admin</option>
            </select>
            <select name="status" class="form-control mb-2">
              <option value="active">Active</option>
              <option value="pending">Pending</option>
              <option value="blocked">Blocked</option>
            </select>
            <button type="submit" name="add_user" class="btn btn-primary">حفظ</button>
          </form>
        </div>

        <form method="GET" class="d-flex mb-3">
          <input type="text" name="user_search" value="<?= htmlspecialchars($user_search) ?>" class="form-control me-2" placeholder="ابحث بالاسم أو الإيميل">
          <button type="submit" class="btn btn-dark">بحث</button>
        </form>

        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th><?= sortLink("user","id","ID",$user_sort_col,$user_sort_dir) ?></th>
              <th><?= sortLink("user","name","الاسم",$user_sort_col,$user_sort_dir) ?></th>
              <th><?= sortLink("user","email","الإيميل",$user_sort_col,$user_sort_dir) ?></th>
              <th><?= sortLink("user","status","الحالة",$user_sort_col,$user_sort_dir) ?></th>
              <th><?= sortLink("user","role","الدور",$user_sort_col,$user_sort_dir) ?></th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($users_result->num_rows > 0): ?>
              <?php while($row = $users_result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><span class="badge bg-<?= $row['status']=="active"?"success":"secondary" ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                  <td><?= htmlspecialchars($row['role']) ?></td>
                  <td>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $row['id'] ?>">تعديل</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                      <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
                      <button type="submit" name="delete_user" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6">لا يوجد نتائج</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <?php renderPagination("user_page", $user_page, $user_total_pages); ?>
      </div>
    </div>

    <div class="tab-pane fade" id="articles">
      <div class="card p-4">
        <h4> جدول المقالات</h4>
        <button class="btn btn-success mb-3" data-bs-toggle="collapse" data-bs-target="#addArticleForm"> إضافة مقال جديد</button>
        <div id="addArticleForm" class="collapse mb-3">
          <form method="POST" class="card card-body">
            <input type="text" name="title" placeholder="العنوان" class="form-control mb-2" required>
            <textarea name="content" placeholder="المحتوى" class="form-control mb-2"></textarea>
            <select name="user_id" class="form-control mb-2" required>
              <?php foreach ($users_for_select as $u): ?>
                <option value="<?= intval($u['id']) ?>"><?= htmlspecialchars($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="status" class="form-control mb-2">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
            <button type="submit" name="add_article" class="btn btn-primary">حفظ</button>
          </form>
        </div>

        <form method="GET" class="d-flex mb-3">
          <input type="text" name="article_search" value="<?= htmlspecialchars($article_search) ?>" class="form-control me-2" placeholder="ابحث بالعنوان أو اسم الكاتب">
          <button type="submit" class="btn btn-dark">بحث</button>
        </form>

        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th><?= sortLink("article","id","ID",$article_sort_col,$article_sort_dir) ?></th>
              <th><?= sortLink("article","title","العنوان",$article_sort_col,$article_sort_dir) ?></th>
              <th><?= sortLink("article","author","المؤلف",$article_sort_col,$article_sort_dir) ?></th>
              <th><?= sortLink("article","status","الحالة",$article_sort_col,$article_sort_dir) ?></th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($articles_result->num_rows > 0): ?>
              <?php while($row = $articles_result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['title']) ?></td>
                  <td><?= htmlspecialchars($row['author']) ?></td>
                  <td><span class="badge bg-<?= $row['status']=="published"?"success":"secondary" ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                  <td>
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editArticleModal<?= $row['id'] ?>">تعديل</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                      <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
                      <button type="submit" name="delete_article" class="btn btn-sm btn-danger">حذف</button>
                    </form>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5">لا يوجد نتائج</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
        <?php renderPagination("article_page", $article_page, $article_total_pages); ?>
      </div>
    </div>

  </div>
</div>

<?php
$res_mod_users = $conn->query("SELECT * FROM users ORDER BY id DESC LIMIT 100");
while ($row = $res_mod_users->fetch_assoc()): ?>
<div class="modal fade" id="editUserModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"> تعديل مستخدم</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
        <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" class="form-control mb-2" required>
        <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" class="form-control mb-2" required>
        <select name="role" class="form-control mb-2">
          <option value="user" <?= $row['role']=="user"?"selected":"" ?>>User</option>
          <option value="editor" <?= $row['role']=="editor"?"selected":"" ?>>Editor</option>
          <option value="admin" <?= $row['role']=="admin"?"selected":"" ?>>Admin</option>
        </select>
        <select name="status" class="form-control mb-2">
          <option value="active" <?= $row['status']=="active"?"selected":"" ?>>Active</option>
          <option value="pending" <?= $row['status']=="pending"?"selected":"" ?>>Pending</option>
          <option value="blocked" <?= $row['status']=="blocked"?"selected":"" ?>>Blocked</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_user" class="btn btn-primary">حفظ التغييرات</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </form>
  </div>
</div>
<?php endwhile; ?>

<?php
$res_mod_articles = $conn->query("SELECT * FROM articles ORDER BY id DESC LIMIT 100");
while ($row = $res_mod_articles->fetch_assoc()): ?>
<div class="modal fade" id="editArticleModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"> تعديل مقال</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" value="<?= intval($row['id']) ?>">
        <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" class="form-control mb-2" required>
        <textarea name="content" class="form-control mb-2" required><?= htmlspecialchars($row['content']) ?></textarea>
        <select name="status" class="form-control mb-2">
          <option value="published" <?= $row['status']=="published"?"selected":"" ?>>Published</option>
          <option value="draft" <?= $row['status']=="draft"?"selected":"" ?>>Draft</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit_article" class="btn btn-primary">حفظ التغييرات</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </form>
  </div>
</div>
<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar(){
  document.querySelector('.sidebar').classList.toggle('collapsed');
  document.querySelector('.content').classList.toggle('collapsed');
}
</script>
</body>
</html>