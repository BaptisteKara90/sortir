// import './bootstrap.js';
// /*
//  * Welcome to your app's main JavaScript file!
//  *
//  * This file will be included onto the page via the importmap() Twig function,
//  * which should already be in your base.html.twig.
//  */
//
//     //menu burger
//     const burger = document.getElementById('burger');
//     const menu = document.getElementById('menu');
//     burger.addEventListener('click', () => {
//         burger.classList.toggle('active');
//         menu.classList.toggle('open');
//     });
//
//     //modal
// let cancel = document.querySelectorAll('.cancel');
// let modalOverlay = document.getElementById('modalOverlay')
// let modalClose = document.querySelector('#closeModalButton')
//
// console.log(cancel)
// cancel.forEach(button => {
//     button.addEventListener('click', function(e) {
//         e.preventDefault();
//         modalOverlay.style.display = 'flex';
//     });
// });
//
// modalClose.addEventListener('click', function(){
//     modalOverlay.style.display = 'none'
// })
//
// //addLieu
//
// let buttonPlus = document.querySelector(".plus-container");
// let formLieu = document.querySelector('#newLieu')
//
//
// buttonPlus.addEventListener('click', function(){
//     formLieu.style.display = formLieu.style.display === 'block' ? 'none' : 'block';
// })