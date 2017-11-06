<template>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i>
          <span class="badge">{{unreadNotifications.length}}</span>
        </a>
        <ul class="dropdown-menu alert-dropdown">
          <li>
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
                    let newUnreadNotifications={data:{tipe:notification.tipe,subject:notification.subject}};
                    this.unreadNotifications.push(newUnreadNotifications);
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
