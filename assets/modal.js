console.log('ça marche ou quoi?')

let cancel = document.querySelector('#cancel');
let modalOverlay = document.getElementById('modalOverlay')
let modalClose = document.querySelector('#closeModalButton')

cancel.addEventListener('click', function(e){
    e.preventDefault()
   modalOverlay.style.display = 'flex'
})

modalClose.addEventListener('click', function(){
    modalOverlay.style.display = 'none'
})