<?php
require_once __DIR__ . '/../config/database.php';

$isLoggedIn         = isLoggedIn();
$userRole           = getCurrentUserRole();
$userName           = $_SESSION['name'] ?? '';
$committeePosition  = $_SESSION['committee_position'] ?? null;
$userType           = $_SESSION['user_type'] ?? null;

$roleLabels = [
    'admin'     => 'ผู้ดูแลระบบ',
    'committee' => 'คณะกรรมการ',
    'advisor'   => 'อาจารย์ที่ปรึกษา',
    'member'    => 'สมาชิกทั่วไป',
];

if (isset($roleLabels[$userRole])) {
    $roleNameThai = $roleLabels[$userRole];
} else {
    $roleNameThai = '';
}

$positionLabels = [
    'president'          => 'ประธานชมรม',
    'vice_president'     => 'รองประธานชมรม',
    'treasurer'          => 'เหรัญญิก',
    'secretary_saluang'  => 'เลขาสะลวง',
    'secretary_wiangbua' => 'เลขาเวียงบัว',
    'nurse'              => 'พยาบาล',
    'venue_coordinator'  => 'ฝ่ายสถานที่',
    'pr'                 => 'ฝ่ายประชาสัมพันธ์',
];

if ($committeePosition !== null && isset($positionLabels[$committeePosition])) {
    $positionNameThai = $positionLabels[$committeePosition];
} else {
    $positionNameThai = null;
}

$isSelfCheckinAvailable = false;

if ($userType === 'student' && $userRole === 'member') {
    try {
        $checkinQuery = $conn->query(
            "SELECT activity_id FROM activities WHERE self_checkin_enabled = 1 AND DATE(start_date) = CURDATE() LIMIT 1"
        );
        if ($checkinQuery && $checkinQuery->num_rows > 0) {
            $isSelfCheckinAvailable = true;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

$currentPageName = basename($_SERVER['PHP_SELF']);

function isActive($pageName) {
    global $currentPageName;
    return ($currentPageName === $pageName) ? 'active' : '';
}

function committeeCanSee($menuName, $positionKey, $role) {
    if ($role === 'admin') return true;
    if ($role === 'advisor') return true;
    if ($role !== 'committee' && $role !== 'leader') return false;
    return committeeCanAccess($menuName);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ระบบสารสนเทศเพื่อการบริหารจัดการชมรมเพื่อนภูผา มหาวิทยาลัยราชภัฏเชียงใหม่">
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <title>ชมรมเพื่อนภูผา มหาวิทยาลัยราชภัฏเชียงใหม่</title>

    <!-- iOS / PWA Fullscreen App Support (ซ่อนแถบ URL และแสดงไอคอนโลโก้ชมรมบน iPhone) -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ชมรมเพื่อนภูผา">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2E7D32">

    <link rel="icon" href="assets/icon/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/variables.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/components.css?v=<?= time() ?>">
<?php
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isAllowedHoverPage = in_array($currentScript, [
    'erp_activities.php',
    'admin_users.php',
    'advisor_members.php'
]);
$bodyHoverClass = $isAllowedHoverPage ? 'allow-btn-hover' : 'no-btn-hover';
?>
<body class="<?= $bodyHoverClass ?>">

<header class="mobile-header hide-on-desktop">
    <button class="btn-icon" id="sidebarToggle" aria-label="เปิดเมนู">
        <i class="fa-solid fa-bars" style="color: var(--color-primary-dark); font-size: 22px;"></i>
    </button>
    <div class="mobile-header-brand">ชมรมเพื่อนภูผา</div>
    <div class="mobile-header-auth" style="position: relative;">
        <?php if ($isLoggedIn): ?>
            <button class="mobile-avatar-btn" id="mobileSidebarTrigger" aria-label="เมนูผู้ใช้">
                <?php
                $cl = preg_replace('/[^a-zA-Zก-๙]/u', '', $userName);
                $fl = mb_substr($cl, 0, 1, 'UTF-8');
                echo $fl ? mb_strtoupper($fl, 'UTF-8') : 'U';
                ?>
            </button>
            <div class="user-profile-popup" id="mobileUserProfilePopup"
                 style="display:none;position:absolute;top:100%;right:0;width:150px;background:white;
                        border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.15);margin-top:10px;
                        padding:8px 0;border:1px solid #eee;z-index:1001;">
                <a href="profile.php" class="popup-item"
                   style="display:flex;align-items:center;gap:12px;padding:10px 16px;
                          color:#333;text-decoration:none;font-size:14px;transition:background 0.2s;">
                    <i class="fa-regular fa-user" style="width:20px;text-align:center;font-size:16px;color:#555;"></i>
                    โปรไฟล์
                </a>
                <div style="height:1px;background:#eee;margin:4px 16px;"></div>
                <a href="logout.php" class="popup-item"
                   style="display:flex;align-items:center;gap:12px;padding:10px 16px;
                          color:#333;text-decoration:none;font-size:14px;transition:background 0.2s;">
                    <i class="fa-solid fa-arrow-right-from-bracket" style="width:20px;text-align:center;font-size:16px;color:#555;"></i>
                    ออกจากระบบ
                </a>
            </div>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary mobile-login-btn">เข้าสู่ระบบ</a>
        <?php endif; ?>
    </div>
</header>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header hide-on-desktop" style="text-align:right;">
        <button class="btn-icon hide-on-desktop" id="sidebarClose" aria-label="ปิดเมนู" style="color:var(--text-muted);">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list" style="padding-top:15px;">

            <li><a href="index.php" class="nav-link <?= isActive('index.php') ?>">หน้าแรก</a></li>

            <li>
                <?php
                $isVol = ($currentPageName === 'activities_by_category.php' && ($_GET['cat'] ?? '') === 'volunteer');
                ?>
                <a href="activities_by_category.php?cat=volunteer" class="nav-link <?= $isVol ? 'active' : '' ?>">กิจกรรมจิตอาสา</a>
            </li>

            <li>
                <?php
                $isSport = ($currentPageName === 'activities_by_category.php' && ($_GET['cat'] ?? '') === 'sport');
                ?>
                <a href="activities_by_category.php?cat=sport" class="nav-link <?= $isSport ? 'active' : '' ?>">กิจกรรมกีฬา</a>
            </li>

            <li>
                <?php
                $isByan = ($currentPageName === 'activities_by_category.php' && ($_GET['cat'] ?? '') === 'byaniyan');
                ?>
                <a href="activities_by_category.php?cat=byaniyan" class="nav-link <?= $isByan ? 'active' : '' ?>">กิจกรรมบายเนียร์</a>
            </li>

            <?php if ($isLoggedIn): ?>

                <?php if ($userRole !== 'advisor'): ?>
                    <li>
                        <a href="joined_activities.php" class="nav-link <?= isActive('joined_activities.php') ?>">กิจกรรมที่เข้าร่วม</a>
                    </li>
                <?php endif; ?>

                <?php if ($userRole !== 'advisor'): ?>
                    <li>
                        <a href="certificates.php" class="nav-link <?= isActive('certificates.php') ?>">เกียรติบัตรของฉัน</a>
                    </li>
                <?php endif; ?>

                <?php if ($isSelfCheckinAvailable && $userType === 'student' && $userRole === 'member'): ?>
                    <li>
                        <a href="self_checkin.php" class="nav-link <?= isActive('self_checkin.php') ?>" style="font-weight:600;">เช็คอินกิจกรรม</a>
                    </li>
                <?php endif; ?>

            <?php endif; ?>

            <li class="nav-divider"></li>
            <li><a href="committee_directory.php" class="nav-link <?= isActive('committee_directory.php') ?>">กรรมการผู้นำรุ่น</a></li>
            <li><a href="about.php" class="nav-link <?= isActive('about.php') ?>">เกี่ยวกับเรา</a></li>
            <li><a href="contact.php" class="nav-link <?= isActive('contact.php') ?>">ติดต่อเรา</a></li>

            <?php if ($isLoggedIn): ?>

                <?php if ($userRole === 'advisor'): ?>
                    <?php
                    // นับจำนวนกิจกรรมที่รออาจารย์อนุมัติ
                    $pendingApprovalCount = 0;
                    try {
                        $pAR = $conn->query("SELECT COUNT(*) as total FROM activities WHERE approval_status = 'pending'");
                        if ($pAR) $pendingApprovalCount = (int)$pAR->fetch_assoc()['total'];
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                    }
                    ?>

                    <!-- เมนูระดับสิทธิ์อาจารย์ที่ปรึกษา (มี 3 เมนูหลัก) -->
                    <li class="nav-group-title">เมนูอาจารย์ที่ปรึกษา</li>
                    <li><a href="advisor_members.php" class="nav-link <?= isActive('advisor_members.php') ?>">ดูข้อมูลสมาชิก</a></li>
                    <li>
                        <a href="advisor_approval.php" class="nav-link nav-link-with-badge <?= isActive('advisor_approval.php') ?>">
                            <span>อนุมัติกิจกรรม</span>
                            <?php if ($pendingApprovalCount > 0): ?>
                                <span class="notification-badge"><?= $pendingApprovalCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="advisor_news.php" class="nav-link <?= isActive('advisor_news.php') ?>">จัดการข่าวประชาสัมพันธ์</a></li>

                <?php endif; ?>

                <?php if ($userRole === 'committee' || $userRole === 'leader'): ?>
                    <li class="nav-group-title">ระบบจัดการองค์กร (ERP)</li>
                    <?php if (committeeCanSee('members', $committeePosition, $userRole)): ?>
                        <li><a href="advisor_members.php" class="nav-link <?= isActive('advisor_members.php') ?>">ดูข้อมูลสมาชิก</a></li>
                    <?php endif; ?>
                    <?php if (committeeCanSee('qr_scanner', $committeePosition, $userRole)): ?>
                        <li><a href="qr_scanner.php" class="nav-link <?= isActive('qr_scanner.php') ?>">สแกนเช็คอินกิจกรรม</a></li>
                    <?php endif; ?>
                    <?php if (committeeCanSee('activities', $committeePosition, $userRole)): ?>
                        <li><a href="erp_activities.php" class="nav-link <?= isActive('erp_activities.php') ?>">จัดการกิจกรรม</a></li>
                    <?php endif; ?>
                    <?php if (committeeCanSee('budgets', $committeePosition, $userRole)): ?>
                        <li><a href="erp_budgets.php" class="nav-link <?= isActive('erp_budgets.php') ?>">บัญชีรายรับ-รายจ่าย</a></li>
                    <?php endif; ?>
                    <?php if (committeeCanSee('vehicles', $committeePosition, $userRole)): ?>
                        <li><a href="erp_vehicles.php" class="nav-link <?= isActive('erp_vehicles.php') ?>">จัดการยานพาหนะ</a></li>
                    <?php endif; ?>
                    <li><a href="advisor_news.php" class="nav-link <?= isActive('advisor_news.php') ?>">จัดการข่าวประชาสัมพันธ์</a></li>
                    <?php if (committeeCanSee('reports', $committeePosition, $userRole)): ?>
                        <li><a href="erp_reports.php" class="nav-link <?= isActive('erp_reports.php') ?>">สรุปรายงานผล</a></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($userRole === 'admin'): ?>
                    <li class="nav-group-title">จัดการระบบ</li>
                    <li><a href="admin_dashboard.php" class="nav-link <?= isActive('admin_dashboard.php') ?>">แดชบอร์ดระบบ</a></li>
                    <li><a href="admin_users.php" class="nav-link <?= isActive('admin_users.php') ?>">จัดการสมาชิก</a></li>
                <?php endif; ?>

            <?php endif; ?>

        </ul>
    </nav>

    <div class="sidebar-footer">
        <?php if ($isLoggedIn): ?>
            <!-- ข้อมูลโปรไฟล์ แสดงเฉพาะหน้าจอคอมพิวเตอร์ (หน้าจอมือถือมีปุ่มโปรไฟล์ที่มุมขวาบนแล้ว) -->
            <div class="user-profile-menu-container hide-on-mobile" style="position:relative;text-align:left;">
                <button class="user-profile-btn" id="userProfileBtn"
                        style="background:none;border:none;padding:0;margin:0;width:100%;
                               display:flex;align-items:center;gap:12px;cursor:pointer;text-align:left;">
                    <div class="user-avatar"
                         style="width:36px;height:36px;background:#9E9E9E;color:white;border-radius:50%;
                                display:flex;align-items:center;justify-content:center;font-size:14px;
                                font-weight:bold;flex-shrink:0;">
                        <?php
                        $cu = preg_replace('/[^a-zA-Zก-๙]/u', '', $userName);
                        $fl = mb_substr($cu, 0, 1, 'UTF-8');
                        echo $fl ? mb_strtoupper($fl, 'UTF-8') : 'U';
                        ?>
                    </div>
                    <div class="user-info-text"
                         style="flex-grow:1;overflow:hidden;display:flex;flex-direction:column;justify-content:center;">
                        <div class="user-name"
                             style="font-size:14px;font-weight:500;color:#333;white-space:nowrap;
                                    overflow:hidden;text-overflow:ellipsis;line-height:1.2;margin-bottom:2px;">
                            <?php
                            if ($userType === 'student') {
                                echo htmlspecialchars($_SESSION['username'] ?? $userName);
                            } else {
                                echo htmlspecialchars($userName);
                            }
                            ?>
                        </div>
                        <div style="font-size:12px;color:#777;line-height:1.2;">
                            <?= $roleNameThai ?>
                            <?php if ($positionNameThai !== null && $userRole !== 'member'): ?>
                                – <?= $positionNameThai ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </button>

                <div class="user-profile-popup" id="userProfilePopup"
                     style="display:none;position:absolute;bottom:100%;left:0;width:100%;background:white;
                            border-radius:12px;box-shadow:0 -4px 20px rgba(0,0,0,0.1);margin-bottom:12px;
                            padding:8px 0;border:1px solid #eee;z-index:1001;">
                    <a href="profile.php" class="popup-item"
                       style="display:flex;align-items:center;gap:12px;padding:10px 16px;
                              color:#333;text-decoration:none;font-size:14px;transition:background 0.2s;">
                        <i class="fa-regular fa-user" style="width:20px;text-align:center;font-size:16px;color:#555;"></i>
                        โปรไฟล์
                    </a>
                    <div style="height:1px;background:#eee;margin:4px 16px;"></div>
                    <a href="logout.php" class="popup-item"
                       style="display:flex;align-items:center;gap:12px;padding:10px 16px;
                              color:#333;text-decoration:none;font-size:14px;transition:background 0.2s;">
                        <i class="fa-solid fa-arrow-right-from-bracket" style="width:20px;text-align:center;font-size:16px;color:#555;"></i>
                        ออกจากระบบ
                    </a>
                </div>
            </div>

            <!-- ปุ่มดาวน์โหลดแอพ แสดงเฉพาะหน้าจอมือถือ แทนที่โปรไฟล์ (ดาวน์โหลดไฟล์ APK ตรงจากเซิร์ฟเวอร์) -->
            <div class="hide-on-desktop app-download-btn-wrapper">
                <a href="download_app.php" class="btn btn-primary btn-block" style="font-size:14px;padding:10px 14px;">
                    <i class="fa-solid fa-download"></i> ดาวน์โหลดแอพ
                </a>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var profileButton = document.getElementById('userProfileBtn');
                var profilePopup  = document.getElementById('userProfilePopup');
                if (profileButton && profilePopup) {
                    profileButton.addEventListener('click', function(e) {
                        e.stopPropagation();
                        profilePopup.style.display = (profilePopup.style.display === 'none' || profilePopup.style.display === '') ? 'block' : 'none';
                    });
                    document.addEventListener('click', function(e) {
                        if (!profilePopup.contains(e.target) && e.target !== profileButton) profilePopup.style.display = 'none';
                    });
                }
                var mobileBtn  = document.getElementById('mobileSidebarTrigger');
                var mobileMenu = document.getElementById('mobileUserProfilePopup');
                if (mobileBtn && mobileMenu) {
                    mobileBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        mobileMenu.style.display = (mobileMenu.style.display === 'none' || mobileMenu.style.display === '') ? 'block' : 'none';
                    });
                    document.addEventListener('click', function(e) {
                        if (!mobileMenu.contains(e.target) && e.target !== mobileBtn) mobileMenu.style.display = 'none';
                    });
                }

                // หากเปิดในแอพพลิเคชันมือถืออยู่แล้ว ให้ซ่อนปุ่มดาวน์โหลดแอพ
                if (typeof AndroidApp !== 'undefined') {
                    var appWrappers = document.querySelectorAll('.app-download-btn-wrapper');
                    appWrappers.forEach(function(el) { el.style.display = 'none'; });
                }
            });
            </script>
            <style>.popup-item:hover { background: #f5f5f5; }</style>

        <?php else: ?>
            <div class="hide-on-mobile">
                <a href="login.php" class="btn btn-primary btn-block" style="font-size:13px;">เข้าสู่ระบบ</a>
                <div class="text-center mt-2">
                    <a href="register.php" class="text-link" style="color:var(--text-muted);font-size:12px;">สมัครสมาชิกใหม่</a>
                </div>
            </div>

            <!-- ปุ่มดาวน์โหลดแอพ แสดงเฉพาะหน้าจอมือถือ (ดาวน์โหลดไฟล์ APK ตรงจากเซิร์ฟเวอร์) -->
            <div class="hide-on-desktop app-download-btn-wrapper">
                <a href="download_app.php" class="btn btn-primary btn-block" style="font-size:14px;padding:10px 14px;">
                    <i class="fa-solid fa-download"></i> ดาวน์โหลดแอพ
                </a>
            </div>
        <?php endif; ?>
    </div>

</aside>

<main class="main-content">

<?php if (isset($_SESSION['alert'])): ?>
    <div id="flash-message"
         data-type="<?= htmlspecialchars($_SESSION['alert']['type'] ?? 'info') ?>"
         data-title="<?= htmlspecialchars($_SESSION['alert']['title'] ?? '') ?>"
         data-text="<?= htmlspecialchars($_SESSION['alert']['text'] ?? '') ?>"
         class="d-none">
    </div>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>
