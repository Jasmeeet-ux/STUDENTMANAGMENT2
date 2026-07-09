<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

$userId = getCurrentUserId();

// Fetch all user notes (including favorited flag)
$stmt = $pdo->prepare("
    SELECT n.id, n.lesson_id, n.content, n.updated_at, n.favorited,
           l.title AS lesson_title,
           c.id AS course_id,
           c.title AS course_title
    FROM notes n
    JOIN lessons l ON n.lesson_id = l.id
    JOIN courses c ON l.course_id = c.id
    WHERE n.user_id = ?
    ORDER BY n.updated_at DESC
");
$stmt->execute([$userId]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Notes - Culture of Internet</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#2563eb',
                secondary: '#3b82f6',
                accent: '#1d4ed8',
                light: '#eff6ff',
                dark: '#1e3a8a'
            }
        }
    }
}
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
  .note-card {
    margin-bottom: 1rem;
  }
  .flex.items-center.justify-between.gap-4.mb-6 {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
}
</style>
</head>
<body class="bg-gray-50 text-gray-800">


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

  <div class="flex-1 w-full min-h-screen ">
  <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
    <h2 class="text-2xl font-bold">My Notes</h2>
    <p class="text-gray-600 text-sm">Review and manage your course notes</p>
  </header>

  <main class="p-6">
    <div class="flex items-center justify-between gap-4 mb-6">
      <div class="flex gap-3">
        <select id="filterFavorite" class="px-3 py-2 border rounded-lg">
          <option value="all">All</option>
          <option value="fav">Favorites</option>
          <option value="nonfav">Not Favorited</option>
        </select>
        <select id="sortBy" class="px-3 py-2 border rounded-lg">
          <option value="updated_desc">Newest</option>
          <option value="updated_asc">Oldest</option>
          <option value="fav_first">Favorites first</option>
        </select>
      </div>

      <div class="flex gap-2">
        <button id="downloadPdf" class="bg-primary text-white px-4 py-2 rounded-lg">Download PDF</button>
        <button id="downloadTxt" class="bg-white border px-4 py-2 rounded-lg">Download TXT</button>
        <button id="exportJson" class="bg-white border px-4 py-2 rounded-lg">Export JSON</button>
      </div>
    </div>

    <?php if (!empty($notes)): ?>
    <div id="notesContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($notes as $note): ?>
      <div class="note-card bg-white shadow-sm rounded-xl p-5 border border-gray-100 transition" 
           data-updated="<?= strtotime($note['updated_at']) ?>"
           data-fav="<?= (int)$note['favorited'] ?>"
           data-content="<?= htmlspecialchars(strtolower($note['content']), ENT_QUOTES) ?>"
           data-lesson="<?= htmlspecialchars(strtolower($note['lesson_title']), ENT_QUOTES) ?>"
           data-course="<?= htmlspecialchars(strtolower($note['course_title']), ENT_QUOTES) ?>"
           data-noteid="<?= $note['id'] ?>">
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="text-lg font-bold"><?= htmlspecialchars($note['lesson_title']) ?></h3>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($note['course_title']) ?></p>
          </div>
          <div class="flex items-center gap-2">
            <button class="favorite-btn" data-id="<?= $note['id'] ?>" aria-label="Toggle favorite">
              <?php if ($note['favorited']): ?>
                <i class="fa-solid fa-star text-yellow-500"></i>
              <?php else: ?>
                <i class="fa-regular fa-star text-gray-400"></i>
              <?php endif; ?>
            </button>
            <button onclick="downloadSingle(<?= $note['id'] ?>)" title="Download note" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-download"></i></button>
            <button onclick="deleteNote(<?= $note['id'] ?>)" class="text-red-500 hover:text-red-700" title="Delete note"><i class="fa-solid fa-trash"></i></button>
          </div>
        </div>

        <textarea id="note-<?= $note['id'] ?>" class="w-full h-40 border border-gray-200 rounded-lg p-2 text-gray-700 resize-none focus:outline-none"><?= htmlspecialchars($note['content']) ?></textarea>

        <div class="flex justify-between items-center mt-3 text-sm text-gray-400">
          <div>Updated <?= date('M d, Y H:i', strtotime($note['updated_at'])) ?></div>
          <div class="flex items-center gap-2">
            <button onclick="saveNote(<?= $note['id'] ?>)" class="px-3 py-1 bg-primary text-white text-sm rounded-lg">Save</button>
            <a href="/bundle/course-player.php?id=<?= $note['course_id'] ?>#lesson-<?= $note['lesson_id'] ?>" class="text-sm text-gray-600 hover:underline">Open lesson</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm p-8 sm:p-12 text-center">
      <p class="text-gray-500 mb-4">You haven’t added any notes yet.</p>
      <a href="my-courses.php" class="bg-primary text-white px-5 py-2 rounded-lg">Go to Courses</a>
    </div>
    <?php endif; ?>
  </main>
</div>

<script>
/* Search / Filter / Sort */

const notes = Array.from(document.querySelectorAll('.note-card'));
const filterFavorite = document.getElementById('filterFavorite');
const sortBy = document.getElementById('sortBy');

function filterAndSort() {
  const favFilter = filterFavorite.value;
  const sort = sortBy.value;

  let filtered = notes.filter(card => {
    const fav = card.dataset.fav === '1';
    if (favFilter === 'fav' && !fav) return false;
    if (favFilter === 'nonfav' && fav) return false;
    return true;
  });

  // sorting
  filtered.sort((a,b) => {
    if (sort === 'updated_desc') return b.dataset.updated - a.dataset.updated;
    if (sort === 'updated_asc') return a.dataset.updated - b.dataset.updated;
    if (sort === 'fav_first') return (b.dataset.fav - a.dataset.fav) || (b.dataset.updated - a.dataset.updated);
    return 0;
  });

  const container = document.getElementById('notesContainer');
  container.innerHTML = '';
  filtered.forEach(c => container.appendChild(c));
}

filterFavorite.addEventListener('change', filterAndSort);
sortBy.addEventListener('change', filterAndSort);

/* Save note (AJAX) */
function saveNote(noteId) {
  const content = encodeURIComponent(document.getElementById('note-' + noteId).value);
  fetch('save-note-dashboard.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id=' + noteId + '&content=' + content
  }).then(r => r.json()).then(data => {
    if (data.success) {
      alert('Saved');
    } else {
      alert('Error saving note');
    }
  });
}

/* Delete */
function deleteNote(noteId) {
  if (!confirm('Delete this note?')) return;
  fetch('delete-note.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id=' + noteId
  }).then(r => r.json()).then(data => {
    if (data.success) location.reload();
    else alert('Delete failed');
  });
}

/* Favorite toggle */
document.querySelectorAll('.favorite-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    fetch('toggle-favorite.php', {
      method:'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id
    }).then(r => r.json()).then(resp => {
      if (resp.success) {
        // toggle icon
        const icon = btn.querySelector('i');
        if (resp.favorited) {
          icon.className = 'fa-solid fa-star text-yellow-500';
          btn.closest('.note-card').dataset.fav = '1';
        } else {
          icon.className = 'fa-regular fa-star text-gray-400';
          btn.closest('.note-card').dataset.fav = '0';
        }
        filterAndSort();
      } else {
        alert('Unable to toggle favorite');
      }
    });
  });
});

/* Download and Export */
document.getElementById('downloadPdf').addEventListener('click', () => {
  window.location.href = 'download-notes.php?format=pdf';
});
document.getElementById('downloadTxt').addEventListener('click', () => {
  window.location.href = 'download-notes.php?format=txt';
});
document.getElementById('exportJson').addEventListener('click', () => {
  window.location.href = 'export-notes.php';
});

function downloadSingle(id) {
  window.location.href = 'download-notes.php?format=txt&single=' + id;
}
</script>
</body>
</script>
<script>
// Sidebar toggle for mobile (no redeclaration)
(function() {
  var sidebar = document.getElementById('sidebar');
  var openSidebar = document.getElementById('openSidebar');
  var closeSidebar = document.getElementById('closeSidebar');
  if (openSidebar && sidebar) {
    openSidebar.addEventListener('click', function() {
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.add('translate-x-0');
      document.body.style.overflow = 'hidden';
    });
  }
  if (closeSidebar && sidebar) {
    closeSidebar.addEventListener('click', function() {
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
      document.body.style.overflow = '';
    });
  }
  document.addEventListener('click', function(e) {
    if (window.innerWidth < 768 && sidebar && !sidebar.contains(e.target) && !openSidebar.contains(e.target)) {
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
      document.body.style.overflow = '';
    }
  });
})();
</script>
</html>
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
