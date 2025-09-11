document.addEventListener("DOMContentLoaded", () => {
    console.log("Galeri Foto loaded");

    // cari semua tombol hapus
    const deleteButtons = document.querySelectorAll(".btn-delete");

    deleteButtons.forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault(); // cegah link langsung jalan
            const url = this.getAttribute("href");

            Swal.fire({
                title: "Yakin mau hapus?",
                text: "Data yang sudah dihapus tidak bisa dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
});
