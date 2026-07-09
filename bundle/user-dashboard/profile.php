<?php
// profile.php - Updated, cleaned UI + avatar upload + secure checks

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// bootstrap / helpers (adjust path if needed)
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

// Helpers from your existing stack expected:
// getCurrentUser(), getCurrentUserId(), loginUser($id,$data), logoutUser()

$user = getCurrentUser();
$userId = getCurrentUserId();

// If session user isn't populated, try fetch from DB to be safe
if (!$user && $userId && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($user) loginUser($userId, $user);
}

// Provide defaults to avoid undefined index warnings
$user = $user ?? [];
$userName = trim($user['name'] ?? '');
$nameParts = explode(' ', $userName, 2);
$firstName = $nameParts[0] ?? '';
$lastName = $nameParts[1] ?? '';
$email = $user['email'] ?? '';
$referralCode = $user['referral_code'] ?? '';
$memberSince = isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : '—';

// Avatar handling - prefer stored avatar field if exists in DB, else use session temp
$avatarSessionKey = 'profile_avatar_temp';
$avatarPath = $_SESSION[$avatarSessionKey] ?? ($user['avatar'] ?? ($user['image_url'] ?? null));
$avatarPublicUrl = $avatarPath ? htmlspecialchars($avatarPath) : null;

// messages
$updateMessage = '';
$passwordMessage = '';
$avatarMessage = '';

// Process personal info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personal'])) {
    $fn = trim($_POST['first_name'] ?? '');
    $ln = trim($_POST['last_name'] ?? '');
    $em = trim($_POST['email'] ?? '');

    if ($fn === '' || $em === '') {
        $updateMessage = ['type' => 'error', 'text' => 'First name and email are required.'];
    } elseif (!filter_var($em, FILTER_VALIDATE_EMAIL)) {
        $updateMessage = ['type' => 'error', 'text' => 'Invalid email address.'];
    } else {
        $fullName = trim($fn . ' ' . $ln);
        try {
            // check email uniqueness
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$em, $userId]);
            if ($stmt->fetch()) {
                $updateMessage = ['type' => 'error', 'text' => 'Email is already in use by another account.'];
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$fullName, $em, $userId]);

                // Refresh session user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $newUser = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($newUser) {
                    loginUser($userId, $newUser);
                    $user = $newUser;
                    $firstName = $fn;
                    $lastName = $ln;
                    $email = $em;
                }

                $updateMessage = ['type' => 'success', 'text' => 'Profile updated successfully.'];
            }
        } catch (PDOException $e) {
            error_log("Profile update error (user $userId): " . $e->getMessage());
            $updateMessage = ['type' => 'error', 'text' => 'Database error while updating profile.'];
        }
    }
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($current === '' || $new === '' || $confirm === '') {
        $passwordMessage = ['type' => 'error', 'text' => 'Please fill all password fields.'];
    } elseif ($new !== $confirm) {
        $passwordMessage = ['type' => 'error', 'text' => 'New passwords do not match.'];
    } elseif (strlen($new) < 6) {
        $passwordMessage = ['type' => 'error', 'text' => 'New password must be at least 6 characters.'];
    } else {
        // Ensure user has hashed password in DB and verify
        $storedHash = $user['password'] ?? null;
        if ($storedHash && password_verify($current, $storedHash)) {
            $newHash = password_hash($new, PASSWORD_BCRYPT);
            try {
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$newHash, $userId]);

                // refresh session
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $newUser = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($newUser) {
                    loginUser($userId, $newUser);
                    $user = $newUser;
                }

                $passwordMessage = ['type' => 'success', 'text' => 'Password updated successfully.'];
            } catch (PDOException $e) {
                error_log("Password update error (user $userId): " . $e->getMessage());
                $passwordMessage = ['type' => 'error', 'text' => 'Database error while updating password.'];
            }
        } else {
            $passwordMessage = ['type' => 'error', 'text' => 'Current password is incorrect.'];
        }
    }
}

// Process avatar upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
    if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        if (!in_array($file['type'], $allowed)) {
            $avatarMessage = ['type' => 'error', 'text' => 'Only JPG/PNG/WebP/GIF allowed.'];
        } elseif ($file['size'] > 4 * 1024 * 1024) {
            $avatarMessage = ['type' => 'error', 'text' => 'Max file size: 4 MB.'];
        } else {
            // ensure upload dir exists
            $uploadDir = __DIR__ . '/uploads/avatars';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safeExt = preg_replace('/[^a-zA-Z0-9]/','', $ext) ?: 'png';
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $safeExt;
            $target = $uploadDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                // web path to file relative to project - adjust if site root differs
                $relativePath = 'bundle/user-dashboard/uploads/avatars/' . $filename;
                $avatarMessage = ['type' => 'success', 'text' => 'Avatar uploaded.'];

                // Save to DB if possible - check columns 'avatar' then 'image_url'
                try {
                    $colToUpdate = null;
                    $colCheck = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'avatar'");
                    $colCheck->execute();
                    if ($colCheck->fetch()) {
                        $colToUpdate = 'avatar';
                    } else {
                        $colCheck2 = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'image_url'");
                        $colCheck2->execute();
                        if ($colCheck2->fetch()) $colToUpdate = 'image_url';
                    }

                    if ($colToUpdate) {
                        $stmt = $pdo->prepare("UPDATE users SET {$colToUpdate} = ? WHERE id = ?");
                        $stmt->execute([$relativePath, $userId]);

                        // refresh session user
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $newUser = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($newUser) {
                            loginUser($userId, $newUser);
                            $user = $newUser;
                            $avatarPath = $newUser[$colToUpdate] ?? $relativePath;
                            $_SESSION[$avatarSessionKey] = $avatarPath;
                            $avatarPublicUrl = htmlspecialchars($avatarPath);
                        }
                    } else {
                        // DB doesn't have a column - store in session so it shows immediately
                        $_SESSION[$avatarSessionKey] = $relativePath;
                        $avatarPath = $relativePath;
                        $avatarPublicUrl = htmlspecialchars($relativePath);
                    }
                } catch (PDOException $e) {
                    error_log("Avatar DB save error (user $userId): " . $e->getMessage());
                    $_SESSION[$avatarSessionKey] = $relativePath;
                    $avatarPath = $relativePath;
                    $avatarPublicUrl = htmlspecialchars($relativePath);
                }
            } else {
                $avatarMessage = ['type' => 'error', 'text' => 'Failed to move uploaded file. Check directory permissions.'];
            }
        }
    } else {
        $avatarMessage = ['type' => 'error', 'text' => 'No file uploaded or upload error.'];
    }
}

// Build avatar URL (handle both absolute and relative paths)
$avatarDisplay = null;
if ($avatarPublicUrl) {
    // if path looks like URL, use directly; else convert to relative / absolute path
    if (filter_var($avatarPublicUrl, FILTER_VALIDATE_URL)) {
        $avatarDisplay = $avatarPublicUrl;
    } else {
        // assume relative path from webroot - adjust if your server runs from different folder
        $avatarDisplay = '/' . ltrim($avatarPublicUrl, '/');
    }
} else {
    // fallback placeholder (initials)
    $initial = strtoupper(substr($firstName ?: ($user['name'] ?? 'U'), 0, 1));
    $avatarDisplay = null;
}

// small helper to render alerts
function alertHtml($m) {
    if (!$m || !is_array($m)) return '';
    $color = $m['type'] === 'success' ? 'green' : 'red';
    return "<div class=\"bg-{$color}-50 border border-{$color}-200 text-{$color}-700 px-4 py-3 rounded mb-4\">".htmlspecialchars($m['text'])."</div>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Profile Settings — Culture of Internet</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script>
tailwind.config = { theme: { extend: { colors: { primary:'#2563eb' } } } };
</script>
<style>
@media (max-width: 1024px) {
  .md\:w-60 {
    width: 0 !important;
  }
  .md\:ml-64 {
    margin-left: 0 !important;
  }
}
@media (max-width: 640px) {
  .p-6 {
    padding: 1rem !important;
  }
  .mx-auto {
    margin-left: 0 !important;
    margin-right: 0 !important;
  }
  .rounded-xl {
    border-radius: 0.75rem !important;
  }
}
</style>
</head>
<body class="bg-gray-50 min-h-screen">

  <div class="min-h-screen flex flex-col md:flex-row">
    <!-- Mobile Sidebar Toggle -->
    <div class="md:hidden flex items-center justify-between bg-white px-4 py-3 border-b border-gray-200 sticky top-0 z-40">
     
      <span class="font-bold text-primary text-lg">Culture of Internet</span>
       <button id="openSidebar" class="text-gray-700 focus:outline-none">
        <i class="fas fa-bars fa-lg"></i>
      </button>
    </div>
    <!-- Sidebar -->
    <div class="md:w-60 w-full fixed md:static z-50 top-0 left-0 h-full md:h-auto">
      <?php include "includes/sidebar.php"; ?>
    </div>

    <!-- Content -->
    <div class="flex-1 w-full ">
      <header class="bg-white border-b sticky top-0 z-20">
        <div class=" mx-auto px-8 py-4">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-xl font-bold text-gray-800">Profile Settings</h2>
              <p class="text-sm text-gray-600">Manage your account information and preferences</p>
            </div>
            <div class="flex items-center space-x-4">
              <div class="text-right text-sm text-gray-500">
                <div>Member since</div>
                <div class="font-medium text-gray-800"><?= htmlspecialchars($memberSince) ?></div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main class="mx-auto p-6">
        <!-- Profile header -->
        <section class="bg-white rounded-xl shadow p-6 mb-6">
          <div class="flex items-center gap-6">
            <div class="relative">
              <?php if ($avatarDisplay): ?>
                <img src="<?= $avatarDisplay ?>" alt="avatar" class="w-24 h-24 rounded-full object-cover border" id="topAvatar">
              <?php else: 
                $initial = strtoupper(substr($firstName ?: ($user['name'] ?? 'U'), 0, 1)); ?>
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary to-blue-400 flex items-center justify-center text-white text-2xl font-bold border" id="topAvatar">
                  <?= htmlspecialchars($initial) ?>
                </div>
              <?php endif; ?>
              <button id="avatarBtn" type="button" class="absolute -bottom-1 -right-1 bg-white border p-2 rounded-full shadow" style="display:block;">
                <i class="fa fa-camera text-gray-600"></i>
              </button>
              <!-- Hidden avatar upload form -->
              <form id="avatarUploadForm" method="POST" enctype="multipart/form-data" style="display:none">
                <input type="file" name="avatar" id="avatarFileInput" accept="image/*">
                <input type="hidden" name="upload_avatar" value="1">
              </form>
              <!-- Show avatar upload message if any -->
              <div id="avatarMsgTop">
                <?= alertHtml($avatarMessage) ?>
              </div>
            </div>

            <div>
              <h3 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($firstName ?: ($user['name'] ?? 'User')) ?></h3>
              <p class="text-sm text-gray-600"><?= htmlspecialchars($email ?: '—') ?></p>
              <div class="mt-3 flex gap-2">
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm"><i class="fa fa-check-circle mr-1"></i> Verified</span>
                <?php if ($referralCode): ?>
                  <span class="bg-blue-50 text-blue-800 px-3 py-1 rounded-full text-sm">Ref: <?= htmlspecialchars($referralCode) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </section>

        <!-- Main panels -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Left / main column -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Tabs -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
              <nav class="flex border-b px-4">
                <button data-tab="personal" class="px-6 py-4 text-primary border-b-2 border-primary font-semibold">Personal</button>
                <button data-tab="security" class="px-6 py-4 text-gray-600 hover:text-primary">Security</button>
                <button data-tab="preferences" class="px-6 py-4 text-gray-600 hover:text-primary">Preferences</button>
              </nav>

              <div class="p-6">
                <!-- Personal -->
                <div id="tab-personal">
                  <?= alertHtml($updateMessage) ?>
                  <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="update_personal" value="1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                        <input name="first_name" value="<?= htmlspecialchars($firstName) ?>" class="w-full px-4 py-2 border rounded" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                        <input name="last_name" value="<?= htmlspecialchars($lastName) ?>" class="w-full px-4 py-2 border rounded" />
                      </div>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                      <input name="email" type="email" value="<?= htmlspecialchars($email) ?>" class="w-full px-4 py-2 border rounded" />
                      <p class="text-xs text-gray-500 mt-1">Used for account notifications.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>" class="w-full px-4 py-2 border rounded" placeholder="City, State">
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                        <select name="timezone" class="w-full px-4 py-2 border rounded">
                          <option selected>Asia/Kolkata (IST)</option>
                          <option>Asia/Dubai</option>
                          <option>Europe/London</option>
                          <option>America/New_York</option>
                        </select>
                      </div>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                      <textarea name="bio" rows="4" class="w-full px-4 py-2 border rounded" placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                      <a href="my-courses.php" class="px-4 py-2 border rounded text-gray-700">Cancel</a>
                      <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Save changes</button>
                    </div>
                  </form>
                </div>

                <!-- Security -->
                <div id="tab-security" class="hidden">
                  <?= alertHtml($passwordMessage) ?>
                  <form method="POST" class="space-y-4">
                    <input type="hidden" name="update_password" value="1">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                      <input name="current_password" type="password" class="w-full px-4 py-2 border rounded" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                        <input name="new_password" type="password" class="w-full px-4 py-2 border rounded" />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
                        <input name="confirm_password" type="password" class="w-full px-4 py-2 border rounded" />
                      </div>
                    </div>
                    <div class="flex justify-end">
                      <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Change password</button>
                    </div>
                  </form>
                </div>

                <!-- Preferences -->
                <div id="tab-preferences" class="hidden">
                  <form class="space-y-6">
                    <div class="flex items-center justify-between">
                      <div>
                        <h4 class="font-semibold">Autoplay next lesson</h4>
                        <p class="text-sm text-gray-500">Start next lesson automatically</p>
                      </div>
                      <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-primary relative"></div>
                      </label>
                    </div>

                    <div class="flex items-center justify-between">
                      <div>
                        <h4 class="font-semibold">Show captions by default</h4>
                        <p class="text-sm text-gray-500">Display captions on video</p>
                      </div>
                      <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-primary relative"></div>
                      </label>
                    </div>

                    <div class="flex justify-end">
                      <button type="button" class="px-4 py-2 bg-primary text-white rounded">Save preferences</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

          </div>

          <!-- Right column (sidebar) -->
          <aside class="space-y-6">
            <div class="bg-white rounded-xl shadow p-6">
              <h4 class="font-semibold mb-2">Account</h4>
              <div class="text-sm text-gray-600">
                <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($email ?: '—') ?></div>
                <div><strong>Member since:</strong> <?= htmlspecialchars($memberSince) ?></div>
              </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
              <h4 class="font-semibold mb-2">Referral</h4>
              <div class="flex gap-2">
                <input id="refCode" readonly value="<?= htmlspecialchars($referralCode ?: '') ?>" class="flex-1 px-3 py-2 border rounded bg-gray-50">
                <button id="copyRef" class="px-3 py-2 bg-primary text-white rounded"><i class="fa fa-copy"></i></button>
              </div>
              <p id="copyFeedback" class="text-xs text-green-600 mt-2 hidden">Copied!</p>
            </div>

            <div class="bg-red-50 rounded-xl p-4 text-sm border border-red-100">
              <h4 class="font-semibold text-red-700 mb-1">Danger Zone</h4>
              <p class="text-red-600">Permanently delete your account and all data. This cannot be undone.</p>
              <form method="POST" onsubmit="return confirm('Are you sure? This action is irreversible.');" class="mt-3">
                <input type="hidden" name="delete_account" value="1">
                <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded">Delete account</button>
              </form>
            </div>
          </aside>
        </div>
      </main>
    </div>
  </div>

<script>
// tab logic
document.querySelectorAll('[data-tab]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    document.querySelectorAll('[data-tab]').forEach(b=>{ b.classList.remove('text-primary','border-b-2','border-primary','font-semibold'); b.classList.add('text-gray-600');});
    btn.classList.add('text-primary','border-b-2','border-primary','font-semibold');
    const tab = btn.getAttribute('data-tab');
    document.getElementById('tab-' + tab).classList.remove('hidden');
    ['personal','security','preferences'].forEach(t=>{
      if (t !== tab) document.getElementById('tab-' + t).classList.add('hidden');
    });
  });
});

// avatar upload by clicking camera icon only
document.getElementById('avatarBtn')?.addEventListener('click', function(e){
  e.preventDefault();
  document.getElementById('avatarFileInput').click();
});
document.getElementById('avatarFileInput')?.addEventListener('change', function(){
  if (this.files.length > 0) {
    document.getElementById('avatarUploadForm').submit();
  }
});

// copy referral
document.getElementById('copyRef')?.addEventListener('click', function(){
  const ref = document.getElementById('refCode');
  if (!ref) return;
  ref.select ? ref.select() : null;
  navigator.clipboard?.writeText(ref.value).then(()=>{
    const fb = document.getElementById('copyFeedback');
    if (fb) { fb.classList.remove('hidden'); setTimeout(()=>fb.classList.add('hidden'),2000); }
  });
});
</script>
<script>
// Sidebar toggle for mobile
const sidebar = document.getElementById('sidebar');
const openSidebar = document.getElementById('openSidebar');
const closeSidebar = document.getElementById('closeSidebar');
if (openSidebar && sidebar) {
  openSidebar.addEventListener('click', () => {
    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('translate-x-0');
    document.body.style.overflow = 'hidden';
  });
}
if (closeSidebar && sidebar) {
  closeSidebar.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    sidebar.classList.remove('translate-x-0');
    document.body.style.overflow = '';
  });
}
// Hide sidebar on click outside (mobile)
document.addEventListener('click', function(e) {
  if (window.innerWidth < 768 && sidebar && !sidebar.contains(e.target) && !openSidebar.contains(e.target)) {
    sidebar.classList.add('-translate-x-full');
    sidebar.classList.remove('translate-x-0');
    document.body.style.overflow = '';
  }
});
</script>
</body>
</html>
