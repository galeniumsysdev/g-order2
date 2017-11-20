<template>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i>
          Notifications
          <span class="badge">{{notifCount}}</span>
        </a>
        <ul class="dropdown-menu alert-dropdown dropdown-notif" role="menu" v-if="unreadNotifications.length">
          <li v-for="item in unreadNotifications" v-bind:key="item.index">
            <a v-bind:href="item.data.href">
              <span class="item">
                <span class="item-left">
                  <span class="item-info">
                    {{item.data.subject}}
                  </span>
                </span>
              </span>
            </a>
          </li>
        </ul>
        <ul class="dropdown-menu alert-dropdown dropdown-notif" role="menu" v-else>
          <li>
            <span class="item">
              <span class="item-left">
                <span class="item-info">
                  No unread notifications
                </span>
              </span>
            </span>
          </li>
        </ul>
        <div style="display: none" id="user-email">{{email}}</div>
    </li>
</template>

<script>
  // import NotificationItem from './NotificationItem.vue'
    export default {
        props:{
                  notif:{
                    type: Array
                  },
                  userid: {
                      type: String
                  },
                  email: {
                    type: String
                  },
                  count: {
                    type: Number
                  }
              },
        data(){
          return {
            unreadNotifications: [],
            notifCount: 0,
          }
        },
        mounted() {
            this.notifCount = this.count;
            this.unreadNotifications = this.notif;
            Echo.channel(this.email)
                .listen('.wk-prod', (notification) => {
                    this.unreadNotifications.push({
                      data: {
                        subject: notification.message,
                        content: {
                          href: notification.href
                        }
                      }
                    });
                    this.notifCount = this.notifCount + 1;
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
