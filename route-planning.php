<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <link rel="stylesheet" href="bootstrap.css">
    <link rel="stylesheet" href="style.css">
    <style>
        p{
            margin: 0;
        }
    </style>
</head>
<body>
<header class="shadow d-flex" id="nav-bar">
    <div class="col-9 d-flex">
        <img src="./icon/mainicon.png" id="logo">
        <h1 class="ln-100">大眾運輸查詢系統</h1>
    </div>
    <div class="col-3 ln-100">
        <button class="btn btn-outline-dark">系統管理</button>
        <button class="btn btn-outline-dark">登出</button>
    </div>
</header>
<div id="app">
    <form class="mt-5 w-75 mx-auto card p-4 mb-3" @submit.prevent="search">
        <h3>路徑規劃</h3>
        <div class="d-flex justify-content-between">
            <div class="form-group w-25">
                <label for="">起點站點</label>
                <select v-model="form.start" id="" class="form-control">
                    <option :value="station.id" v-for="station in stations">{{ station.name }}</option>
                </select>
            </div>
            <div class="form-group w-25">
                <label for="">終點站點</label>
                <select v-model="form.end" id="" class="form-control">
                    <option :value="station.id" v-for="station in stations">{{ station.name }}</option>
                </select>
            </div>
            <div class="form-group w-25">
                <label for="">排序方式</label>
                <select v-model="form.sort" id="" class="form-control">
                    <option value="fastest">最快速</option>
                    <option value="fewest">最少轉車</option>
                </select>
            </div>
        </div>
        <button class="btn btn-primary">搜尋</button>
        <p class="text-secondary text-center" v-if="results.length == 0">沒有找到符合路徑</p>
        <div class="card p-2 mt-3" style="cursor: pointer;" v-for="(result,i) in results"
             @click="showDetail(i)" v-else>
            <h6>計畫 {{i + 1}}</h6>
            <span>行駛時間: {{ result.totalTime }}</span>
            <span>轉車次數: {{ result.transferCount }}</span>
        </div>
    </form>
    <div class="modal fade" id="detail">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h4>方案詳細資訊</h4>
                    <p>行駛時間: {{ plan.totalTime }}</p>
                    <p>轉車次數: {{ plan.transferCount }}</p>
                    <h5>路線詳細資訊</h5>
                    <div class="mt-3" v-for="detail in plan.routeStack">
                        <h6>路線名稱: {{detail.route}}</h6>
                        <p>起點站點: {{ detail.startStation }}</p>
                        <p>終點站點: {{ detail.endStation }}</p>
                        <p>行駛時間: {{ detail.time }}</p>
                    </div>
                    <button class="btn btn-outline-dark mt-5" onclick="back()">關閉</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<script src="jqueryv3.7.1.js"></script>
<script src="bootstrap.js"></script>
<script src="js.js"></script>
<script src="vue.3.3.3.js"></script>
<script>
    const {createApp} = Vue
    createApp({
        data() {
            return {
                stations: [],
                results: [],
                form: {},
                planId: "",
                plan: [],
            }
        },
        methods: {
            getStations() {
                $.post("api/get.php", {action: 'station'}, (res) => {
                    this.stations = JSON.parse(res)
                })
            },
            search() {
                if (this.form.start == null || this.form.end == null || this.form.sort == null){
                    alert("請輸入正確資料")
                    return
                }
                $.post("api/search.php",{form:this.form},(res)=>{
                    console.log(this.form)
                    console.log(res)
                    this.results = res
                    if (this.form.sort === 'fewest') {
                        this.empty = false;
                        this.results.sort((a,b)=>
                            (a.transferCount - b.transferCount) || (a.totalTime - b.totalTime)
                        )
                    } else {
                        this.results.sort((a,b)=>
                            (a.totalTime - b.totalTime) || (a.transferCount - b.transferCount)
                        )
                    }
                })
            },
            showDetail(planId) {
                $("div.modal#detail").modal("show")
                this.plan = this.results[planId]
                console.log(this.plan)
            }
        },
        mounted(){
            this.getStations()
        }
    }).mount("#app")
</script>
