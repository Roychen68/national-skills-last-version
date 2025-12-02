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
        <div class="card card-body shadow w-75 mt-5 mx-auto">
            <div class="card-header d-flex">
                <select id="start" class="form-control w-25 mr-5" v-model="start">
                    <option disabled>請選擇站點</option>
                    <option :value="item.name" v-for="item in stations">{{item.name}}</option>
                </select>
                <select id="end" class="form-control w-25" v-model="end">
                    <option disabled>請選擇站點</option>
                    <option :value="item.name" v-for="item in stations">{{item.name}}</option>
                </select>
            </div>
        </div>
    </div>
</body>
</html>
<script src="jqueryv3.7.1.js"></script>
<script src="jquery-uiv1.13.2.js"></script>
<script src="bootstrap.js"></script>
<script src="vue.3.5.16.js"></script>
<script src="js.js"></script>
<script>
    const {createApp} = Vue3516
    createApp({
        data(){
            return{
                stations: [],
                station: "",
                form: {}
            }
        },
        methods: {
            fetch(){
                $.post("api/get.php",{action: "station"},(res)=>{
                    this.stations = JSON.parse(res)
                    this.station = this.stations[0].name
                })
            },
            submit(){
                $.post("api/add.php",{form: this.form,action: "response"},(res)=>{
                    alert(res)
                })
            }
        },
        mounted(){
            this.fetch()
        }
    }).mount("#app")
</script>