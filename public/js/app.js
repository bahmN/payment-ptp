function updatePage() {
    const input = document.getElementById('search');

    console.log(input.value.length);

    if (input.value.length == 0) {
        location.reload();
    }
}