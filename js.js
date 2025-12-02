check()
function logout() {
    $.post("api/logout.php",function () {
        location.href = "index.html"    
    })
}
function check() {
    $.post("api/session.php",function (res) {
        if (res == true) {
            $("#logout-button").show()
        } else {
            $("#logout-button").hide()
        }
    })
}
function admin() {
    $.post("api/session.php",function (res) {
        if (res == true) {
            location.href = "route.html"
        } else {
            $("div.modal").modal("show")
        }
    })
}
function back() {
    $("div.modal").modal("hide")
}
$("div.logo").click(function () {
    location.href = "index.html"
})
count()
function count() {
    $.post("api/get.php",{action: "response"},function (res) {
        const data = JSON.parse(res)
        $("#responsesetting-link .count-badge").remove()
        $("#responsesetting-link").append(`
        <span class="count-badge">${data.length}</span>
        `)
    })
}