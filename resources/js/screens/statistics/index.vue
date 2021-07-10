<script type="text/ecmascript-6">
import StylesMixin from './../../mixins/entriesStyles';

export default {
    mixins: [
        StylesMixin,
    ],
}
</script>
<script type="text/ecmascript-6">
import $ from 'jquery';
import _ from 'lodash';
import axios from 'axios';

export default {
    props: [
        'resource', 'title'
    ],

    /**
     * The component's data.
     */
    data() {
        return {
            statistics: {},
            updateTimer: 5000,
            updateTimout:  null,
            ready: false,
            enabled: false,
            recordingStatus: 'enabled',
            firstTime: true
        };
    },

    /**
     * Prepare the component.
     */
    mounted() {
        document.title = "Statistics - Telescope";
        this.enabled = true;
        this.loadData();
    },


    /**
     * Clean after the component is destroyed.
     */
    destroyed() {
        clearTimeout(this.updateTimout);
        this.enabled = false;
        document.onkeyup = null;
    },
    methods: {
        afterLoadData() {
            if (this.enabled) {
                this.updateTimout = setTimeout(this.loadData, this.updateTimer);
            }
        },
        loadData(){
            if (this.$root.autoLoadsNewEntries || this.firstTime) {
                axios.post(Telescope.basePath + '/telescope-api/statistics')
                    .then(response => {
                        this.firstTime = false;
                        this.statistics = response.data.statistics;
                        this.recordingStatus = response.data.status;
                        this.ready = true;
                        this.afterLoadData();
                    })
            } else {
                this.afterLoadData();
            }
        },
    }
}
</script>
<template>
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5>Statistics</h5>
        </div>
        <p v-if="recordingStatus !== 'enabled'" class="mt-0 mb-0 disabled-watcher d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 90 90" class="mr-2">
                <path fill="#FFFFFF" d="M45 0C20.1 0 0 20.1 0 45s20.1 45 45 45 45-20.1 45-45S69.9 0 45 0zM45 74.5c-3.6 0-6.5-2.9-6.5-6.5s2.9-6.5 6.5-6.5 6.5 2.9 6.5 6.5S48.6 74.5 45 74.5zM52.1 23.9l-2.5 29.6c0 2.5-2.1 4.6-4.6 4.6 -2.5 0-4.6-2.1-4.6-4.6l-2.5-29.6c-0.1-0.4-0.1-0.7-0.1-1.1 0-4 3.2-7.2 7.2-7.2 4 0 7.2 3.2 7.2 7.2C52.2 23.1 52.2 23.5 52.1 23.9z"></path>
            </svg>
            <span class="ml-1" v-if="recordingStatus == 'disabled'">Telescope is currently disabled.</span>
            <span class="ml-1" v-if="recordingStatus == 'paused'">Telescope recording is paused.</span>
            <span class="ml-1" v-if="recordingStatus == 'off'">This watcher is turned off.</span>
        </p>

        <div v-if="!ready" class="d-flex align-items-center justify-content-center card-bg-secondary p-5 bottom-radius">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="icon spin mr-2 fill-text-color">
                <path d="M12 10a2 2 0 0 1-3.41 1.41A2 2 0 0 1 10 8V0a9.97 9.97 0 0 1 10 10h-8zm7.9 1.41A10 10 0 1 1 8.59.1v2.03a8 8 0 1 0 9.29 9.29h2.02zm-4.07 0a6 6 0 1 1-7.25-7.25v2.1a3.99 3.99 0 0 0-1.4 6.57 4 4 0 0 0 6.56-1.42h2.1z"></path>
            </svg>

            <span>Scanning...</span>
        </div>
        <table id="indexScreen" class="table table-hover table-sm mb-0 penultimate-column-left">
            <tbody>
                <tr v-for="item in statistics" :key="item.id">
                    <td>{{ item.label }}</td>
                    <td>{{ item.value }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
