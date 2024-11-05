<?php
if (isset($_POST['posting'])) {
    $content = $_POST['content'];

    //jika gambar ingin diubah
    if (!empty($_FILES['foto']['name'])) {
        $nama_foto = $_FILES['foto']['name'];
        $ukuran_foto = $_FILES['foto']['size'];

        // ubah

        //tipe foto, png,jpg,etc
        $ext = array('png', 'jpg', 'jpeg', 'jfif');
        $extFoto = pathinfo($nama_foto, PATHINFO_EXTENSION);

        //JIKA EXTENTION FOTO TIDAK ADA EXT YANG TERDAFTAR DI ARRAY EXT
        if (!in_array($extFoto, $ext)) {
            echo "extention tidak sesuai";
            die;
        } else {
            //pindahkan gambar dari tmp folder ke folder yang sudah kita buat
            //unlink() : mendelete file
            unlink('upload/' . $rowTweet['foto']);
            move_uploaded_file($_FILES['foto']['tmp_name'], 'upload/' . $nama_foto);

            $insert = mysqli_query($koneksi, "INSERT INTO tweet (content, id_user,foto) VALUES('$content','$id_user','$nama_foto')");
        }
    } else {
        //gambar tidak mau diubah
        $insert = mysqli_query($koneksi, "INSERT INTO tweet (content, id_user) VALUES('$content','$id_user')");
    }
    header("location:?pg=profil&tweet-berhasil");
}

$queryPosting = mysqli_query($koneksi, "SELECT tweet.* FROM tweet WHERE id_user = '$id_user'");
?>
<div class="row">
    <div class="col-sm-12" align="right">
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#tweet">Tweet</button>
    </div>
    <div class="col-sm-12 mt-3">
        <?php while ($rowPosting = mysqli_fetch_assoc($queryPosting)): ?>
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <img src="upload/<?php echo !empty($rowUser['foto']) ? $rowUser['foto'] : 'https://placehold.co/100' ?>" alt="..." width="100" class="border border-2 rounded-circle">
                </div>
                <div class="flex-grow-1 ms-3">
                    <?php if (!empty($rowPosting['foto'])): ?>
                        <img src="upload/<?php echo $rowPosting['foto'] ?>" alt="" width="500">
                    <?php endif ?>
                    <?php echo $rowPosting['content'] ?>
                </div>
                <!-- LIKE -->
                <div class="status mt-1 ms-3">
                    <input type="text" id="user_id_like" value="<?php echo $rowPosting['id_user'] ?>">
                    <button class="btn btn-success btn-sm" onclick="toggleLike(<?php echo $rowPosting['id']; ?>">Like (0)</button>
                </div>
                <div class="flex-grow-1 ms-3">
                    <form action="add_comment.php" method="POST">
                        <input type="text" name="status_id" value="<?php echo $rowPosting['id'] ?>">
                        <input type="text" name="user_id" value="<?php echo $rowPosting['id_user'] ?>">
                        <textarea name="comment_text" class="form-control" id="comment_text" cols="5" rows="5" placeholder="Tulis Balasan"></textarea>
                        <button class="btn btn-primary btn-sm mt-2" type="submit">Kirim Balasan</button>
                    </form>

                    <div class="mt-2 alert" id="comment-alert" style="display: none;"></div>
                    <div class="mt-1">
                        <?php
                        if (isset($rowPosting['id']) && isset($rowPosting['id_user'])) {
                            $idStatus = $rowPosting['id'];
                            $userId = $rowPosting['id_user'];
                            $queryComment = mysqli_query($koneksi, "SELECT * FROM comments WHERE status_id = '$idStatus' AND user_id = '$userId'");
                            $rowCounts = mysqli_fetch_all($queryComment, MYSQLI_ASSOC);

                            foreach ($rowCounts as  $rowCount) {
                        ?>
                                <span>
                                    <pre>Komentar : <?php echo $rowCount['comment_text'] ?> </pre>
                                </span>

                        <?php
                            }
                        }
                        ?>
                    </div>

                </div>
            </div>
            <hr>
        <?php endwhile ?>

    </div>
</div>

<div class="modal fade" id="tweet" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 " id="exampleModalLabel">Tweet</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <textarea name="content" id="summernote" class="form-control" placeholder="Apa yang sedang hangat dibicarakan?"></textarea>
                    </div>
                    <div class="mb-3">
                        <input type="text" value="<?php echo $rowUser['nama_pengguna'] ?>" class="form-control" placeholder="Username" name="nama_pengguna">
                    </div>
                    <div class="mb-3">
                        <input type="email" value="<?php echo $rowUser['email'] ?>" class="form-control" placeholder="Email" name="email">
                    </div>
                    <div class="mb-3">
                        <input type="file" name="foto">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" name="posting">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function toggleLike(statusId) {
    const userId = document.getElementById('user_id_like').value;
    fetch("like_status.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `status_id=${statusId}&user_id=${userId}`
            
        })
        
        .then(response => response.json())
        .then(data => {
            if (data.status === "liked") {
                alert("Liked!");
            } else if (data.status === "unliked") {
                alert("Unliked!");
            }
            location.reload();
        })
        .catch(error => console.error("Error:", error));
}
</script>
<!-- <script>
    document.getElementById('comment-form').addEventListener('submit', function(e) {e.preventDefault();
        const formData = new FormData(this);

        fetch("add_comment.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertBox = document.getElementById('comment-alert');
                if (data.status === "success") {
                    alertBox.className = "alert alert-success";
                    alertBox.innerHTML = data.message;

                    // bersihkan textarea
                    document.getElementById('comment_text').value = '';
                    location.reload();
                } else {
                    alertBox.className = "alert alert-danger";
                    alertBox.innerHTML = data.message;
                }
                alertBox.style.display = "block";
            })
            .catch(error => console.error('Error:', error));
    })
</script> -->