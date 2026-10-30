<style>
.zalo-icon {
    position: fixed;
    bottom: 20px; /* to-top button hidden: sit at the corner */
    right: 20px;
    z-index: 1000; /* Ensures it stays on top of other content */
    transition: bottom 0.3s ease;
}

/* to-top button visible (scroll > offset): lift above it */
.zalo-icon.zalo-icon--raised {
    bottom: 80px;
}

@media (max-width: 1024px) {
    .zalo-icon.zalo-icon--raised {
        bottom: 70px;
    }
}

@media (max-height: 300px) {
    .zalo-icon,
    .zalo-icon.zalo-icon--raised {
        bottom: 20px !important;
    }
}

.zalo-icon img {
    width: 50px; /* Adjust size as needed */
    height: 50px;
    border-radius: 50%; /* Optional: makes it circular */
    transition: transform 0.3s ease; /* Optional: hover effect */
}

.zalo-icon img:hover {
    transform: scale(1.1); /* Optional: slight zoom on hover */
}
</style>
<div class="zalo-icon">
    <a href="https://zalo.me/0368571310" target="_blank">
        <img src="{{ asset('img/zalo.svg') }}" alt="Zalo Chat">
    </a>
</div>
