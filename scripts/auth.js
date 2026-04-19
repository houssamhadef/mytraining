
async function login(event) {
    event.preventDefault();
    let email = document.getElementById("email").value;
let password = document.getElementById("password").value;
const response = await fetch("http://localhost:8000/traitement.php?action=login", {
    method: "POST",
    headers: {
        "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({
        email: email,
        password: password
    })
});
    const data = await response.json();
    if (data.success){
        // window.location.href = "http://localhost:5500/main.html"
        console.log(data)
    }else {
        throw new Error(data.message);
    }
}