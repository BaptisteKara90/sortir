console.log("Working?")

let buttonPlus = document.querySelector(".plus-container");
let formLieu = document.querySelector('#newLieu')


buttonPlus.addEventListener('click', function(){
    formLieu.style.display = formLieu.style.display === 'block' ? 'none' : 'block';
})