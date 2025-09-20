// Particle settings - Change these values to see what you can make this canvas do!
let maxRadius = 100;
let fadeOutOpacity = 0.025;
let radiusIncrementMax = 0.9;
let velocityIncrementMax = 1;
let randRadiusMax = 20;
let amtParticles = 20;

const arcIncrement = 0.05;



// const img = new Image();
// img.src = 'https://www.johngreengo.com/wp-content/uploads/2021/12/JG-micro-round-RGB2-2000.png';

////////////////////////////////////////////////////////

const canvas = document.querySelector('canvas');
let mousePos = {
    x: -500,
    y: -500
};

canvas.height = window.innerHeight;
canvas.width = window.innerWidth;

let ctx = canvas.getContext('2d');

let particlesArray = [];

function Particle(x, y, radius) {
    let posOrNeg = Math.random() + 0.1 > 0.5 ? '+' : '-';
    let randNumX = `${posOrNeg}${Math.random() * velocityIncrementMax + 0.5}`;
    let randNumY = `${posOrNeg}${Math.random() * velocityIncrementMax + 0.5}`;
    let radiusIncrement = Math.random() * radiusIncrementMax;

    this.radius = radius;
    this.x = x;
    this.y = y;
    this.startAngle = 0;
    this.endAngle = 2 * Math.PI;
    this.arcVal = Math.PI * 2;
    // this.antiClockwise = false;
    this.antiClockwise = Math.random() > 0.5;

    this.opacity = 1;

    this.rVal = Math.floor(Math.random() * 255) + 1;
    this.gVal = Math.floor(Math.random() * 255) + 1;
    this.bVal = Math.floor(Math.random() * 255) + 1;
  
    // this.rVal = 72;
    // this.gVal = 158;
    // this.bVal = 68;

    this.xVel = parseInt(randNumX);
    this.yVel = parseInt(randNumY);

    this.draw = () => {
        this.opacity -= fadeOutOpacity;
        this.x += this.xVel;
        this.y += this.yVel;
        this.radius += radiusIncrement;
        const maxRad = 2 * Math.PI;
        const currentRad = arcIncrement * Math.PI;

        if (this.radius <= 0) this.radius = 0.01;
      
        // ctx.drawImage(img, this.x, this.y, this.radius, this.radius);
      
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.radius, this.startAngle, this.arcVal, this.antiClockwise);
        ctx.strokeStyle = `rgba(${this.rVal}, ${this.gVal}, ${this.bVal}, ${this.opacity})`;
        ctx.stroke();

        if (this.opacity <= 0) {
            let index = particlesArray.indexOf(this);
            particlesArray.splice(index, 1);
        }
    }

}

canvas.addEventListener('mousemove', e => {
    mousePos = {
        x: e.clientX,
        y: e.clientY
    };

    for (let i = 0; i < amtParticles; i++) {
        let randRadius = Math.floor(Math.random() * randRadiusMax);
        particlesArray.push(new Particle(mousePos.x, mousePos.y, randRadius));
    }
});


function animate() {
    ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
    particlesArray.forEach(particle => particle.draw());
    requestAnimationFrame(animate);

}

animate();