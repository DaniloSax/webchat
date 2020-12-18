<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="bg-white overflow-hidden shadow-xl sm:rounded-lg flex"
          style="min-height: 400px; max-height: 400px"
        >
          <!-- list users -->
          <div
            class="w-3/12 bg-gray-200 bg-opacity-25 border-r border-gray-200 shadow-md overflow-y-scroll"
          >
            <ul>
              <li
                v-for="(user, i) in users"
                :key="i"
                @click="loadMessages(user.id)"
                class="p-6 text-lg text-gray-600 leading-7 font-semibold border-b border-gray-200 hover:bg-gray-200 hover:bg-opacity-50 hover:cursor-pointer"
                :class="{ 'bg-gray-200': userActive.name === user.name }"
              >
                <p class="flex items-center">
                  {{ user.name }}
                  <span
                    class="ml-2 w-2 h-2 bg-blue-500 rounded-full"
                    v-if="user.notification"
                  >
                  </span>
                </p>
              </li>
            </ul>
          </div>

          <!-- box messages -->
          <div class="w-9/12 flex flex-col justify-between">
            <!-- message -->
            <div class="w-full p-6 flex flex-col overflow-y-scroll">
              <div
                v-for="message in messages"
                :key="message.id"
                :class="message.from === $page.auth.user.id ? 'text-right' : ''"
                class="w-full mb-3 message"
              >
                <p
                  :class="
                    message.from === $page.auth.user.id
                      ? 'messageFromMe'
                      : 'messageToMe'
                  "
                  class="inline-block p-2 rounded-md"
                  style="max-width: 75%"
                >
                  {{ message.content }}
                </p>
                <span class="block mt-1 text-xs text-gray-500">{{
                  message.created_at | formatDate
                }}</span>
              </div>
            </div>

            <div
              class="w-full bg-gray-200 bg-opacity-25 p-6 border-t border-gray-200"
            >
              <form>
                <div
                  class="flex rounded-md overflow-hidden border border-gray-300"
                >
                  <input
                    :disabled="!userActive.id"
                    v-model="message"
                    type="text"
                    class="flex-1 px-4 py-2 text-sm focus:outline-none"
                  />
                  <button
                    type="submit"
                    :disabled="!userActive.id"
                    @click.prevent="submitMessage"
                    @keypress.enter="submitMessage"
                    :class="{ disabled: !userActive.id || !message }"
                    class="bg-indigo-500 hover:bg-indigo-600 px-4 py-2 text-white"
                  >
                    Enviar
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import Vue from "vue";

export default {
  components: {
    AppLayout,
  },
  data() {
    return {
      users: [],
      messages: [],
      userActive: {},
      message: null,
    };
  },
  methods: {
    scrollToButton() {
      if (this.messages.length) {
        document.querySelectorAll(".message:last-child")[0].scrollIntoView();
      }
    },

    async loadMessages(user_id) {
      await axios.get(`api/users/${user_id}`).then((response) => {
        this.userActive = response.data.user;
      });

      await axios.get(`api/messages/${user_id}`).then((response) => {
        this.messages = response.data.messages;
      });

      const user = this.users.filter((user) => {
        return user.id === user_id;
      });

      if (user) {
        Vue.set(user[0], "notification", false);
      }

      this.scrollToButton();
    },

    async submitMessage() {
      const message = {
        from: this.$page.auth.user.id,
        to: this.userActive.id,
        content: this.message,
      };

      await axios.post("api/messages", message).then((response) => {
        if (response.data.message) {
          this.message = null;
          this.messages.push(response.data.message);
        }
      });
      this.scrollToButton();
    },
  },

  mounted() {
    axios.get("api/users").then((response) => {
      this.users = response.data.users;
    });

    Echo.private(`user.${this.$page.auth.user.id}`).listen(
      ".SendMenssage",
      async (resp) => {
        if (this.userActive && this.userActive.id === resp.menssage.from) {
          await this.messages.push(resp.menssage);
          this.message = null;
          this.scrollToButton();
        } else {
          const user = this.users.filter((user) => {
            return user.id === resp.menssage.from;
          });

          if (user) {
            Vue.set(user[0], "notification", true);
          }
        }
      }
    );
  },
};
</script>


<style scoped>
.messageFromMe {
  @apply bg-indigo-300 bg-opacity-25;
}

.messageToMe {
  @apply bg-gray-300 bg-opacity-25;
}

.disabled {
  cursor: inherit;
  @apply bg-gray-400 bg-opacity-25;
}
</style>