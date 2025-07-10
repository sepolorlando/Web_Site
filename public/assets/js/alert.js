function showAlert(icon,mesage, title) {
    Swal.fire({
        icon: icon,
        title: title,
        text: mesage,
        timer: 2000,
        showConfirmButton: false
    });
}