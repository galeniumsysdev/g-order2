<template>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i>
          <span class="badge">{{unreadNotifications.length}}</span>
        </a>
        <ul class="dropdown-menu alert-dropdown dropdown-notif" role="menu" >
          <li v-for="item in unreadNotifications" v-bind:key="item.index">
            <a v-bind:href="item.href">
              <span class="item">
                <span class="item-left">
                  <span class="item-info">
                    <strong>{{item.title}}</strong>
                    {{item.message}}
                  </span>
                </span>
              </span>
            </a>
          </li>
        </ul>
        <div style="display: none" id="user-email">{{email}}</div>
    </li>
</template>

<script>
  // import NotificationItem from './NotificationItem.vue'
    export default {
        props:{
                  unreads:{
                    type: Array
                  },
                  userid: {
                      type: String
                  },
                  email: {
                    type: String
                  }
              },
        data(){
          return {
            unreadNotifications: []
          }
        },
        mounted() {
            Echo.channel(this.email)
                .listen('.wk-prod', (notification) => {
                    this.unreadNotifications.push(notification);
                    Notification.requestPermission( permission => {
                      let notif = new Notification(notification.title || 'Judul', {
                        body: notification.message, // content for the alert
                        icon: "https://pusher.com/static_logos/320x320.png" // optional image url
                      });
                      // link to page on clicking the notification
                      notif.onclick = () => {
                        window.open(notification.href);
                      };
                  });
                });

        }
    }
</script>
