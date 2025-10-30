document.addEventListener('DOMContentLoaded', () => {
    const mediaContainers = document.querySelectorAll('.post-media-container.video');
    mediaContainers.forEach(container => {
        const video = container.querySelector('video');
        const playButton = container.querySelector('.video-overlay');
        if (video && playButton) {
            playButton.addEventListener('click', () => {
                if (video.paused) {
                    video.play();
                    playButton.style.opacity = '0'; 
                    playButton.style.pointerEvents = 'none'; 
                } else {
                    video.pause();
                    playButton.style.opacity = '1'; 
                    playButton.style.pointerEvents = 'auto';
                }
            });

            video.addEventListener('click', () => {
                video.pause();
                playButton.style.opacity = '1'; 
                playButton.style.pointerEvents = 'auto';
            });
        }
    });

    console.log('All media handlers initialized.');

    const loginForm = document.getElementById('loginForm');
    const loginMessage = document.getElementById('loginMessage');
    const loginButton = document.getElementById('submitBtn');

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const loginUrl = loginForm.getAttribute('action');
            const redirectUrl = loginForm.getAttribute('data-redirect');
            
            loginButton.disabled = true;
            loginButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
            loginMessage.innerHTML = ''; 

            const formData = new FormData(loginForm);
            
            console.log("Mencoba kirim data form:");
            for (let pair of formData.entries()) {
                console.log(pair[0]+ ': ' + pair[1]); 
            }

            try {
                const response = await fetch(loginUrl, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Jaringan bermasalah atau server error');
                }

                const result = await response.json();

                if (result.success) {
                    loginMessage.innerHTML = `<div class="alert alert-success" role="alert">${result.message}</div>`;
                    setTimeout(() => {
                        window.location.href = result.redirectUrl; 
                    }, 1000); 

                } else {
                    loginMessage.innerHTML = `<div class="alert alert-danger" role="alert">${result.message}</div>`;
                }

            } catch (error) {
                console.error('AJAX Error:', error);
                loginMessage.innerHTML = `<div class="alert alert-danger" role="alert">Terjadi kesalahan teknis. Coba lagi.</div>`;
            
            } finally {
                loginButton.disabled = false;
                loginButton.innerHTML = 'Login';
            }
        });
    }
});

function deletePost(postId, is_admin_action = false) {
    const confirmationMessage = is_admin_action 
        ? "APAKAH ANDA YAKIN ingin menghapus postingan ini?"
        : "Apakah Anda yakin ingin menghapus postingan ini?";
        
    if (!confirm(confirmationMessage)) {
        return; 
    }

    const formData = new FormData();
    formData.append('post_id', postId);
    const endpoint = '/karyaKita/sql/delete_process.php'

    fetch(endpoint, {
        method: 'POST',
        body: formData
    })

    .then(response => {
        if (!response.ok) {
            throw new Error('Jaringan bermasalah atau server error');
        }
        return response.json();
    })

    .then(result => {
        alert(result.message);
        if (result.success) {
            const postElement = document.querySelector(`.post[data-id="${postId}"]`);
            if(postElement) {
                postElement.remove();
            } else {
                window.location.reload(); 
            }
        }
    })

    .catch(error => {
        console.error('AJAX Delete Error:', error);
        alert(`Terjadi kesalahan teknis saat menghapus: ${error.message}. Coba lagi.`);
    });
}

function setReportPostId(postId) {
    document.getElementById('report_post_id').value = postId;
}

document.addEventListener('DOMContentLoaded', function() {
    const reportForm = document.getElementById('reportForm');

    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(reportForm);
            const submitBtn = reportForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim...';

            fetch('sql/report_process.php', {
                method: 'POST',
                body: formData
            })

            .then(response => {
                const reportModal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
                reportModal.hide(); 
                
                if (!response.ok) {
                    throw new Error('Jaringan bermasalah atau server error');
                }
                return response.json();
            })

            .then(result => {
                alert(result.message);
                if (result.success) {
                    reportForm.reset();
                }
            })

            .catch(error => {
                console.error('AJAX Report Error:', error);
                alert(`Gagal mengirim laporan: ${error.message}.`);
            })

            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Kirim Laporan';
            });
        });
    }
});