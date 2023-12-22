let root = document.documentElement;

// Initalize property for window width
root.style.setProperty("--windowWidth", window.innerWidth + "px");

// On reside adjust variable within main.scss
function resizeListener() {
  root.style.setProperty("--windowWidth", window.innerWidth + "px");
}

window.addEventListener("resize", resizeListener);
