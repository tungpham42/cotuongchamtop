<style>
.zalo-icon {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000; /* Ensures it stays on top of other content */
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
@media (max-width: 1024px) {
    .zalo-icon {
        bottom: 70px;
        right: 20px;
    }
}
</style>
<div class="zalo-icon">
    <a href="https://zalo.me/0368571310" target="_blank">
        <img src="{{ url('/') }}/img/zalo.svg" alt="Zalo Chat">
    </a>
</div>