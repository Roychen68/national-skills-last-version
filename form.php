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
        <form class="shadow card card-body mt-5 ml-auto mr-auto w-50" @submit.prevent="submit">
            <div class="form-group">
                <label for="" class="text-danger">信箱</label>
                <input type="text" class="form-control response-email" name="email" v-model="form.mail" required>
            </div>
            <div class="form-group">
                <label for="">路線</label>
                <select name="route" id="route" class="form-control response-route" v-model="form.route" required>
                    <option selected disabled>請選擇路線</option>
                    <option :value="item.id" v-for="item in routes">{{item.name}}</option>
                </select>
            </div>
            <div class="form-group">
                <label for="">名字</label>
                <input type="text" class="form-control response-name" name="name" v-model="form.name" required>
            </div>
            <div class="form-group">
                <label for="">搭乘評價</label>
                <br>
                <label>
                    <input type="radio" name="rate" value="非常不滿意" v-model="form.rate" required>
                    非常不滿意
                </label>
                <label>
                    <input type="radio" name="rate" value="不滿意" v-model="form.rate">
                    不滿意
                </label>
                <label>
                    <input type="radio" name="rate" value="普通" v-model="form.rate">
                    普通
                </label>
                <label>
                    <input type="radio" name="rate" value="滿意" v-model="form.rate">
                    滿意
                </label>
                <label>
                    <input type="radio" name="rate" value="非常滿意" v-model="form.rate">
                    非常滿意
                </label>
            </div>
            <div class="form-group">
                <label for="">寶貴意見</label>
                <textarea type="text" class="form-control response-note" name="note" v-model="form.note"></textarea>
            </div>
            <button class="btn btn-outline-primary w-25" style="left: 75% !important;position: relative;">儲存</button>
        </form>
    </div>
</body>
</html>
<script src="jqueryv3.7.1.js"></script>
<script src="jquery-uiv1.13.2.js"></script>
<script src="bootstrap.js"></script>
<script src="vue.3.3.3.js"></script>
<script src="js.js"></script>
<script>
    const {createApp} = Vue
    createApp({
        data(){
            return{
                routes: [],
                route: "",
                form: {}
            }
        },
        methods: {
            fetchRoutes(){
                $.post("api/get.php",{action: "route"},(res)=>{
                    this.routes = JSON.parse(res)
                    this.route = this.routes[0].id
                })
            },
            submit(){
                $.post("api/add.php",{form: this.form,action: "response"},(res)=>{
                    alert(res)
                })
            }
        },
        mounted(){
            this.fetchRoutes()
        }
    }).mount("#app")
</script>