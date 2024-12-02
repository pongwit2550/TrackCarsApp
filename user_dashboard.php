<?php
session_start();
require_once 'db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วและเป็นผู้ใช้ทั่วไป
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

$id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้
$sql_user = 'SELECT * FROM "User" WHERE user_id = $1';
$user_result = pg_query_params($conn, $sql_user, array($id));

if ($user_result === false || pg_num_rows($user_result) === 0) {
    echo "No user data found.";
    exit();
}

$user = pg_fetch_assoc($user_result);

// ดึงข้อมูลบันทึกเวลาของผู้ใช้
$sql_records = 'SELECT * FROM "Record_Time" WHERE user_id = $1 ORDER BY date DESC, time DESC';
$record_result = pg_query_params($conn, $sql_records, array($id));
$records = $record_result ? pg_fetch_all($record_result) : [];

// คำนวณ 2 ช่วงเวลาที่บันทึกบ่อยที่สุด
$sql_time_stats = 'SELECT time, COUNT(time) as count FROM "Record_Time" WHERE user_id = $1 GROUP BY time ORDER BY count DESC LIMIT 2';
$time_stats_result = pg_query_params($conn, $sql_time_stats, array($id));

$time_stats = [];
if ($time_stats_result) {
    while ($row = pg_fetch_assoc($time_stats_result)) {
        $time_stats[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* CSS ที่มีอยู่ก่อนหน้า */
    </style>
</head>
<body>
    <div>
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">TrackCarsApplication</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php"></a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <img src="./uploads/profile/<?php echo htmlspecialchars($user['user_img']); ?>" class="rounded-circle profile-img me-2" width="50" height="50" alt="Profile Image">
                    <h5 class="text-light me-3"><?php echo htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']); ?></h5>
                    <a class="btn btn-danger" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card p-4 mb-4">
            <center>
                <h1 class="card-title text-center">ข้อมูลผู้ใช้</h1>
                <a href="user_edit_img.php?id=<?php echo $user['user_id']; ?>">
                    <img src="./uploads/profile/<?php echo htmlspecialchars($user['user_img']); ?>" class="rounded-circle profile-img mt-3" width="120" height="120" alt="Profile Image">
                </a>
            </center>
            <div class="card mt-3">
                <p class="mt-5 ms-5"><strong>ชื่อ:</strong> <?php echo htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']); ?></p>
                <p class="mt-2 ms-5"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="mt-2 ms-5"><strong>อายุ:</strong> <?php echo htmlspecialchars($user['age']); ?></p>
                <p class="mt-2 ms-5"><strong>ทะเบียนรถ:</strong> <?php echo htmlspecialchars($user['car_registration']); ?></p>
                <a href="car_edit_img.php?id=<?php echo $user['user_id']; ?>">
                  <img src="./uploads/car/<?php echo htmlspecialchars($user['car_registration_img']); ?>" class="rounded profile-img mt-2 ms-5" width="250" height="250" alt="Car Image">
                </a>
                <a class="btn btn-success mt-3 ms-5 me-5 mb-5" href="user_edit.php?id=<?php echo $user['user_id']; ?>">Edit</a>
            </div>
        </div>

        <div class="card p-4 mb-4">
            <h3 class="card-title">Most Frequent Record Times</h3>
            <?php if (!empty($time_stats)) { ?>
                <ul class="list-group">
                    <?php foreach ($time_stats as $stat) { ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>Time:</strong> <?php echo htmlspecialchars($stat['time']); ?></span>
                            <span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($stat['count']); ?></span>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>No record time statistics available.</p>
            <?php } ?>
        </div>

        <h4 class="text-center mt-4">บันทึกเวลาการเข้าออกของคุณ</h4>
        <table class="table table-hover table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Record ID</th>
                    <th>Time</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)) {
                    foreach ($records as $record) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['record_id']); ?></td>
                            <td><?php echo htmlspecialchars($record['time']); ?></td>
                            <td><?php echo htmlspecialchars($record['date']); ?></td>
                        </tr>
                    <?php }
                } else {
                    echo "<tr><td colspan='3'>No records found.</td></tr>";
                } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
