<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>大眾運輸查詢系統</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="bootstrap.css">
</head>

<body>
    <header class="shadow d-flex" id="navbar">
        <div class="d-flex col-10 logo">
            <img src="./icon/mainicon.png" alt="" id="logo">
            <h1 class="ln-100">大眾運輸查詢系統</h1>
        </div>
    </header>
    <div id="app">
        <div class="shadow card mt-5 ml-auto mr-auto w-75">
            <div class="card-header d-flex align-items-center">
                <div class="form-group w-25 mr-3">
                    <label for="">起始站點</label>
                    <select name="route" id="route" class="form-control" v-model="start" required>
                        <option selected disabled>請選擇站點</option>
                        <option :value="item.id" v-for="item in stations">{{item.name}}</option>
                    </select>
                </div>
                <div class="form-group w-25">
                    <label for="">抵達站點</label>
                    <select name="route" id="route" class="form-control" v-model="end" required>
                        <option selected disabled>請選擇站點</option>
                        <option :value="item.id" v-for="item in stations">{{item.name}}</option>
                    </select>
                </div>
                <button class="btn btn-outline-primary mt-3 ml-3 mr-5" @click="search">查詢</button>
                <div class="form-group w-25 ml-5">
                    <label for="">優先順序</label>
                    <select name="route" id="route" class="form-control" v-model="sequence">
                        <option></option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="card card-body shadow" v-for="item in results">
                    <h3>路線: {{item.route}}</h3>
                    <p>需要時間:</p>
                    <p>{{item.time}}</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<script src="jqueryv3.7.1.js"></script>
<script src="jquery-uiv1.13.2.js"></script>
<script src="bootstrap.js"></script>
<script src="vue.3.3.3.js"></script>
<script src="js.js"></script>
<script>
    const { createApp } = Vue
    createApp({
        data() {
            return {
                routes: [],
                route: "",
                stations: "",
                start: "",
                end: "",
                form: {},
                sequence: "",
                results: [],
            }
        },
        methods: {
            fetchRoutes() {
                $.post("api/get.php", { action: "route" }, (res) => {
                    this.routes = JSON.parse(res)
                    this.route = this.routes[0].id
                })
            },
            fetchStations() {
                $.post("api/get.php", { action: "station" }, (res) => {
                    this.stations = JSON.parse(res)
                })
            },
            submit() {
                $.post("api/add.php", { form: this.form, action: "response" }, (res) => {
                    alert(res)
                })
            },
            search(){
                if (this.start == this.end) {
                    alert("兩個站點不得重複")
                } else {
                    $.post("api/get.php",{action: "search",start:this.start,end:this.end},(res)=>{
                        this.results = JSON.parse(res)
                    })
                }
            }
        },
        mounted() {
            this.fetchRoutes()
            this.fetchStations()
        }
    }).mount("#app")
</script>