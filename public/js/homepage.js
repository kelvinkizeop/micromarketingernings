// this section is for info section toggle effect
document.addEventListener('DOMContentLoaded', () => {
    const infoItems = document.querySelectorAll('.info-item'); // Select all info items

    infoItems.forEach(item => {
        const infoTitle = item.querySelector('.info-title');
        const content = item.querySelector('.info-content');
        const arrow = item.querySelector('.arrow');
        const xIcon = item.querySelector('.x-icon');

        // Toggle content visibility and icon when the title is clicked
        infoTitle.addEventListener('click', () => {
            // Toggle the active class to show/hide content
            item.classList.toggle('active');

            // Toggle the open class to rotate the arrow and show/hide X icon
            item.classList.toggle('open');

            // Toggle content visibility
            if (item.classList.contains('active')) {
                content.style.display = 'block'; // Show content
            } else {
                content.style.display = 'none'; // Hide content
            }
        });
    });
});
//this section is for menu toggling effects
// Get the hamburger icon and nav menu
const hamburger= document.querySelector('.hamburger');
const navList = document.querySelector('.nav-list');
const dropdownMenu = document.querySelector('.dropdown-menu');
const closeIcon = document.querySelector('.close-icon');
 // Toggle the dropdown menu when the hamburger is clicked
 hamburger.addEventListener('click', () => {
    dropdownMenu.classList.add('show'); // Add the class to show the dropdown with animation
    hamburger.classList.add('hide');   // Hide hamburger icon
});
// Close the dropdown menu when the close icon (X) is clicked
closeIcon.addEventListener('click', () => {
    dropdownMenu.classList.remove('show');// Remove the class to hide the dropdown with animation
    hamburger.classList.remove('hide');  // Show the hamburger icon again
});
// this section Adds sticky class to header when user scrolls down
window.onscroll = function() {
    let header = document.querySelector('.header');
    if (window.scrollY > 50) {
        header.classList.add('sticky');
    } else {
        header.classList.remove('sticky');
    }
};

// this section is for the testimonial couresel
const testimonials = document.querySelector('.testimonials');
const testimonialsList = document.querySelectorAll('.testimonial');
const prevBtn = document.createElement('button');
const nextBtn = document.createElement('button');

// Create Previous Button
prevBtn.className = 'carousel-btn prev';
prevBtn.innerHTML = '&larr;';
document.querySelector('.testimonial-box').appendChild(prevBtn);

// Create Next Button
nextBtn.className = 'carousel-btn next';
nextBtn.innerHTML = '&rarr;';
document.querySelector('.testimonial-box').appendChild(nextBtn);

let currentIndex = 0;

function updateCarousel() {
    const offset = currentIndex * -100; // Move by 100% of the width
    testimonials.style.transform = `translateX(${offset}%)`;
}

prevBtn.addEventListener('click', () => {
    currentIndex = (currentIndex > 0) ? currentIndex - 1 : testimonialsList.length - 1;
    updateCarousel();
});

nextBtn.addEventListener('click', () => {
    currentIndex = (currentIndex < testimonialsList.length - 1) ? currentIndex + 1 : 0;
    updateCarousel();
});