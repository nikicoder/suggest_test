<template>
    <div class="container">
        <div class="row" style="margin-top: 150px;">
            <div class="col">&nbsp;</div>
            <div class="col-6">
                <form>
                    <div class="form-group">
                        <label for="input-place">Местоположение</label>
                        <input  type="text" 
                                class="form-control" 
                                id="input-place"
                                v-model="place"
                                v-on:keyup="setRequestBlock(false)">
                        <div v-show="suggest_data" class="suggest-menu">
                            <a v-for="item in suggest_data" 
                                :key="item.id" 
                                v-on:click="setMenuItem(item.name)"
                                class="dropdown-item"
                                style="cursor: pointer">
                                    {{item.name}}
                            </a>
                        </div>
                        <small v-show="!suggest_data" class="form-text text-muted">Введите наименование населенного пункта, местности и т.д.</small>
                    </div>
                </form>
            </div>
            <div class="col">&nbsp;</div>
        </div>
    </div>
</template>

<style>
.suggest-menu {
    position: relative;
    top: 10;
    left: 10;
    z-index: 1000;
    float: left;
    min-width: 10rem;
    padding: .5rem 0;
    margin: .125rem 0 0;
    font-size: 1rem;
    color: #212529;
    text-align: left;
    list-style: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: .25rem;
}
</style>

<script>

    import axios from 'axios'
    import settings from '../settings'

    // эта переменная блокирует запросы при выборе из подсказок
    // и разблокируется при любом нажатии кнопки внутри input
    let request_block = true;

    // для таймера автоматического убирания подсказок
    let sugg_menu_timer = null;
    export default {
        data() {
            return {
                place: '',
                suggest_data: null
            }
        },
        watch: {
            place: function() {
                this.debouncedGetSuggest()
            }
        },
        created: function() {
            this.debouncedGetSuggest = _.debounce(this.getSuggests, 500)
        },
        methods: {
            getSuggests: function() {
                // минимальная санитизация: убрать & и ? из текста
                let raw_query = this.place;
                let sanitized = raw_query.replace(/[\?\&]/g,'');
                // подсказки начинаются после ввода 3-го символа
                if(sanitized.length > 2 && request_block === false) {
                    
                    axios.get(settings.api_url + 'suggest/' + sanitized)
                    .then(response => {
                        this.updateSuggestsMenu(response.data);
                    })
                    .catch(function (error) {
                        console.log('Error API call!', error);
                    })
                }
            },
            updateSuggestsMenu: function(data) {
                if(Array.isArray(data) && data.length > 0) {
                    this.suggest_data = data.slice(0, settings.max_items_per_list);
                    // через 6 секунд меню автоматически уберется
                    sugg_menu_timer = setTimeout(() => {
                        this.suggest_data = null 
                    }, 6000);
                }
            },
            setMenuItem: function(data) {
                request_block = true;
                sugg_menu_timer = null;
                this.place = data;
                this.suggest_data = null;
            },
            setRequestBlock: function(state) {
                request_block = state;
            }
        }
    }
</script>
