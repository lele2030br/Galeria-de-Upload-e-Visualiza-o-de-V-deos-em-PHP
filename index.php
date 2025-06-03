<?php
// Caminho da pasta de uploads
$uploadDir = __DIR__ . '/uploads/';
// Extensões permitidas
$allowedExtensions = ['mp4', 'webm', 'ogv'];
// Tamanho máximo (em bytes) - 100MB
$maxFileSize = 100 * 1024 * 1024;

// Cria a pasta de uploads se não existir
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Mensagem de feedback para o usuário
$message = "";

// Processa o upload se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $file = $_FILES['video'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "Erro ao enviar arquivo.";
    } else {
        $fileSize = $file['size'];
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');

        // Validação de extensão
        if (!in_array($extension, $allowedExtensions)) {
            $message = "Tipo de arquivo não permitido. Envie um vídeo .mp4, .webm ou .ogv.";
        }
        // Validação de tamanho
        elseif ($fileSize > $maxFileSize) {
            $message = "Arquivo muito grande. O limite é de 100MB.";
        }
        // Validação de MIME type (extra segurança)
        elseif (
            !in_array(
                mime_content_type($file['tmp_name']),
                ['video/mp4', 'video/webm', 'video/ogg', 'application/octet-stream']
            )
        ) {
            $message = "O arquivo enviado não parece ser um vídeo válido.";
        }
        else {
            // Gera nome único para o arquivo
            $uniqueName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            $destination = $uploadDir . $uniqueName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $message = "Upload realizado com sucesso!";
            } else {
                $message = "Falha ao salvar o arquivo.";
            }
        }
    }
}

// Função para listar vídeos da pasta uploads
function listVideos($uploadDir, $allowedExtensions) {
    $videos = [];
    if (is_dir($uploadDir)) {
        $files = scandir($uploadDir);
        foreach ($files as $file) {
            $filePath = $uploadDir . $file;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (is_file($filePath) && in_array($ext, $allowedExtensions)) {
                $videos[] = [
                    'name' => $file,
                    'size' => filesize($filePath),
                ];
            }
        }
    }
    // Ordena do mais recente para o mais antigo
    usort($videos, function($a, $b) use ($uploadDir) {
        return filemtime($uploadDir.$b['name']) - filemtime($uploadDir.$a['name']);
    });
    return $videos;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Vídeos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google Fonts para modernidade -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --bg: #f4f6fb;
            --card-bg: #fff;
            --radius: 14px;
        }
        html, body {
            padding: 0;
            margin: 0;
            background: var(--bg);
            font-family: 'Roboto', Arial, sans-serif;
            color: #222;
        }
        .container {
            max-width: 900px;
            margin: 30px auto 0 auto;
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 32px 24px 32px 24px;
            box-shadow: 0 4px 32px #0001, 0 1.5px 8px #0001;
        }
        h1 {
            text-align: center;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        .upload-section {
            background: #eef1fa;
            padding: 24px;
            border-radius: var(--radius);
            margin-bottom: 36px;
            box-shadow: 0 0.5px 4px #0001;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .upload-section label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
        }
        .upload-section input[type=file] {
            margin-bottom: 12px;
        }
        .upload-section input[type=submit] {
            background: var(--primary);
            border: none;
            color: #fff;
            padding: 10px 26px;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.18s;
            font-weight: 700;
        }
        .upload-section input[type=submit]:hover {
            background: var(--primary-dark);
        }
        #progressBarContainer {
            display: none;
            margin-top: 10px;
            width: 100%;
        }
        #progressBar {
            width: 100%;
            height: 18px;
            border-radius: 8px;
            background: #e0e7ff;
        }
        #progressStatus {
            font-size: 0.97em;
            margin-left: 8px;
            color: #444;
        }
        .message {
            margin: 22px 0 10px 0;
            padding: 14px 20px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1.1em;
            box-shadow: 0 0.5px 3px #0001;
        }
        .success { background: #e0ffe7; color: #186a2b; border-left: 6px solid #34c759;}
        .error { background: #ffeaea; color: #a50000; border-left: 6px solid #ff6262;}

        .video-gallery {
            margin-top: 36px;
        }
        .video-gallery h2 {
            font-size: 1.5em;
            font-weight: 700;
            color: #3730a3;
            margin-bottom: 20px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 28px;
        }
        .video-card {
            background: #f5f7fd;
            border-radius: var(--radius);
            box-shadow: 0 2px 14px #0001;
            padding: 16px 10px 18px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow .18s;
            position: relative;
            min-height: 270px;
        }
        .video-card:hover {
            box-shadow: 0 8px 24px #0002;
        }
        .video-thumb {
            width: 100%;
            max-width: 220px;
            height: 130px;
            background: #c7d2fe;
            border-radius: 10px;
            margin-bottom: 12px;
            object-fit: cover;
        }
        .video-title {
            font-weight: 500;
            color: #353d6c;
            font-size: 1.02em;
            margin-bottom: 2px;
            text-align: center;
            word-break: break-all;
        }
        .video-size {
            color: #6b7280;
            font-size: .97em;
            margin-bottom: 7px;
        }
        .video-actions {
            margin-top: auto;
        }
        .view-btn {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 6px 20px;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.16s;
            cursor: pointer;
        }
        .view-btn:hover, .view-btn:focus {
            background: var(--primary-dark);
        }
        @media (max-width: 600px) {
            .container {
                padding: 14px 4vw 24px 4vw;
            }
            .upload-section {
                padding: 12px 4vw;
            }
            .video-gallery h2 {
                font-size: 1.2em;
            }
            .gallery-grid {
                gap: 18px;
            }
            .video-card {
                padding: 10px 2px 10px 2px;
                min-height: 180px;
            }
        }
        /* Modal */
        .modal-bg {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0; top: 0; width: 100vw; height: 100vh;
            background: rgba(33,40,79,0.55);
            align-items: center;
            justify-content: center;
        }
        .modal-bg.active {
            display: flex;
        }
        .modal-content {
            background: #fff;
            border-radius: 16px;
            padding: 26px 10px 16px 10px;
            max-width: 96vw;
            max-height: 90vh;
            box-shadow: 0 12px 40px #0003;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            animation: modalpop .25s;
        }
        @keyframes modalpop {
            from { transform: scale(.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-content video {
            width: 92vw;
            max-width: 650px;
            max-height: 56vw;
            border-radius: 10px;
            background: #e0e7ff;
        }
        .close-modal {
            position: absolute;
            top: 14px; right: 18px;
            background: #f2f2f2;
            border: none;
            border-radius: 50%;
            width: 36px; height: 36px;
            font-size: 1.7em;
            color: #4f46e5;
            cursor: pointer;
            box-shadow: 0 1px 4px #0001;
            transition: background .16s;
        }
        .close-modal:hover { background: #e0e7ff;}
        @media (max-width: 400px) {
            .modal-content video {
                width: 90vw;
                max-width: 100vw;
                max-height: 44vw;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <h1>Galeria de Vídeos</h1>

    <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, "sucesso") !== false) ? "success" : "error"; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Formulário AJAX com barra de progresso -->
    <form id="videoUploadForm" class="upload-section" action="" method="post" enctype="multipart/form-data" autocomplete="off">
        <label for="video">Selecione um vídeo (MP4, WEBM, OGV) – até 100MB:</label>
        <input type="file" name="video" id="video" accept=".mp4,.webm,.ogv,video/*" required>
        <input type="submit" value="Enviar">
        <div id="progressBarContainer">
            <progress id="progressBar" value="0" max="100"></progress>
            <span id="progressStatus"></span>
        </div>
    </form>

    <script>
    document.getElementById('videoUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var fileInput = document.getElementById('video');
        if (fileInput.files.length === 0) return;

        var formData = new FormData();
        formData.append('video', fileInput.files[0]);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);

        var progressBarContainer = document.getElementById('progressBarContainer');
        var progressBar = document.getElementById('progressBar');
        var progressStatus = document.getElementById('progressStatus');
        progressBarContainer.style.display = 'block';
        progressBar.value = 0;
        progressStatus.textContent = '';

        xhr.upload.onprogress = function(evt) {
            if (evt.lengthComputable) {
                var percent = Math.round((evt.loaded / evt.total) * 100);
                progressBar.value = percent;
                progressStatus.textContent = percent + '%';
            }
        };
        xhr.onload = function() {
            if (xhr.status == 200) {
                // Atualiza a página para mostrar a mensagem e lista de vídeos
                window.location.reload();
            } else {
                progressStatus.textContent = 'Erro ao enviar o vídeo!';
            }
        };
        xhr.onerror = function() {
            progressStatus.textContent = 'Erro de conexão!';
        };
        xhr.send(formData);
    });
    </script>

    <div class="video-gallery">
        <h2>Vídeos Enviados</h2>
        <?php
        $videos = listVideos($uploadDir, $allowedExtensions);
        if (count($videos) == 0) {
            echo "<p style='color:#666;margin-top:8px;'>Nenhum vídeo enviado ainda.</p>";
        } else {
            echo '<div class="gallery-grid">';
            foreach ($videos as $video) {
                $filename = $video['name'];
                $sizeMB = number_format($video['size'] / (1024 * 1024), 2) . " MB";
                $title = htmlspecialchars($filename);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $mimeType = "video/mp4";
                if ($ext === "webm") $mimeType = "video/webm";
                if ($ext === "ogv") $mimeType = "video/ogg";
                $url = 'uploads/' . rawurlencode($filename);

                // Usa o próprio vídeo como "thumb", carregando poster só se for pequeno
                echo '<div class="video-card">';
                echo '<video class="video-thumb" preload="metadata" muted poster="" src="' . $url . '#t=0.2" tabindex="-1"></video>';
                echo '<div class="video-title">' . $title . '</div>';
                echo '<div class="video-size">' . $sizeMB . '</div>';
                echo '<div class="video-actions">';
                echo '<button class="view-btn" data-video="'.$url.'" data-title="'.$title.'" data-mime="'.$mimeType.'">Visualizar</button>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>

    <!-- Modal de visualização -->
    <div class="modal-bg" id="modalBg">
        <div class="modal-content">
            <button class="close-modal" id="closeModal" aria-label="Fechar">&times;</button>
            <h3 id="modalTitle" style="text-align:center; margin:0 0 10px 0;"></h3>
            <video id="modalVideo" controls style="margin-bottom:12px;" poster=""></video>
        </div>
    </div>

    <script>
    // Modal 
    const modalBg = document.getElementById('modalBg');
    const modalVideo = document.getElementById('modalVideo');
    const modalTitle = document.getElementById('modalTitle');
    const closeModal = document.getElementById('closeModal');

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const src = this.getAttribute('data-video');
            const title = this.getAttribute('data-title');
            const mime = this.getAttribute('data-mime');
            modalVideo.src = src;
            modalVideo.type = mime;
            modalTitle.textContent = title;
            modalBg.classList.add('active');
            modalVideo.load();
            setTimeout(() => { modalVideo.play(); }, 300);
        });
    });
    closeModal.onclick = function() {
        modalBg.classList.remove('active');
        modalVideo.pause();
        modalVideo.src = '';
    };
    modalBg.onclick = function(e) {
        if (e.target === modalBg) {
            modalBg.classList.remove('active');
            modalVideo.pause();
            modalVideo.src = '';
        }
    };
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') {
            modalBg.classList.remove('active');
            modalVideo.pause();
            modalVideo.src = '';
        }
    });
    </script>

</div>
</body>
</html>