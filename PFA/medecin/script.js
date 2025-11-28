const header = document.querySelector('.header');
const menuBtn = document.getElementById('menu-btn');
const nav = document.getElementById('navbar');

window.addEventListener('scroll', () => {
  if (window.scrollY > 50) {
    header.classList.add('scrolled');
  } else {
    header.classList.remove('scrolled');
  }
});

menuBtn.addEventListener('click', () => {
  nav.classList.toggle('active');
});


let currentIndex = 0;
const wrapper = document.getElementById("sliderWrapper");
const totalSlides = wrapper.children.length;
const leftArrow = document.getElementById("leftArrow");
const rightArrow = document.getElementById("rightArrow");

function moveSlide(direction) {
currentIndex += direction;
if (currentIndex < 0) currentIndex = 0;
if (currentIndex >= totalSlides - 1) currentIndex = totalSlides - 1;
wrapper.style.transform = `translateX(-${currentIndex * 100}%)`;
updateArrows();
}

function updateArrows() {
leftArrow.disabled = currentIndex === 0;
rightArrow.disabled = currentIndex === totalSlides - 1;
}

updateArrows(); // init