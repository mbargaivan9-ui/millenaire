{{-- resources/views/pwa/offline.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hors ligne — Millenaire Connect</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; color: #fff; text-align: center; padding: 20px; }
        .card { background: rgba(255,255,255,.15); backdrop-filter: blur(12px); border-radius: 24px; padding: 48px 40px; max-width: 420px; width: 100%; border: 1px solid rgba(255,255,255,.2); }
        .icon { font-size: 5rem; display: block; margin-bottom: 24px; animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        h1 { font-size: 1.8rem; font-weight: 800; margin-bottom: 12px; }
        p { opacity: .85; line-height: 1.6; margin-bottom: 24px; }
        .btn { background: rgba(255,255,255,.2); color: #fff; border: 2px solid rgba(255,255,255,.4); padding: 12px 28px; border-radius: 50px; font-size: 1rem; cursor: pointer; font-weight: 600; transition: all .2s; text-decoration: none; display: inline-block; }
        .btn:hover { background: rgba(255,255,255,.35); }
        .cached-pages { margin-top: 32px; text-align: left; }
        .cached-pages h3 { font-size: .9rem; text-transform: uppercase; letter-spacing: 1px; opacity: .7; margin-bottom: 12px; }
        .cached-link { display: block; padding: 10px 16px; background: rgba(255,255,255,.1); border-radius: 10px; color: #fff; text-decoration: none; margin-bottom: 8px; font-size: .9rem; transition: all .2s; }
        .cached-link:hover { background: rgba(255,255,255,.2); color: #fff; }
    </style>
</head>
<body>
<div class="card">
    <span class="icon">📶</span>
    <h1>Vous êtes hors ligne</h1>
    <p>Pas de connexion Internet détectée. Certaines pages mises en cache restent accessibles.</p>

    <button class="btn" onclick="tryReconnect()">🔄 Réessayer</button>

    <div class="cached-pages">
        <h3>Pages disponibles hors ligne</h3>
        <a href="/student/progress" class="cached-link">🎓 Ma Progression</a>
        <a href="/parent/monitoring" class="cached-link">👨‍👩‍👧 Suivi Scolaire</a>
        <a href="/teacher/bulletin" class="cached-link">📋 Mes Bulletins</a>
    </div>
</div>

<script>
function tryReconnect() {
    if (navigator.onLine) {
        window.location.href = '/';
    } else {
        const btn = document.querySelector('.btn');
        btn.textContent = '❌ Toujours hors ligne...';
        setTimeout(() => { btn.textContent = '🔄 Réessayer'; }, 2000);
    }
}

// Rediriger automatiquement quand la connexion revient
window.addEventListener('online', () => {
    setTimeout(() => window.location.href = '/', 500);
});
</script>
</body>
</html>
