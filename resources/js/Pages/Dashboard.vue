<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted } from "vue";

const message = ref("");
const form = useForm({
    message: "",
    receiver_id: null, // You need to specify the recipient user ID
});

const users = ref([]);
const selectedUser = ref(null);
const messages = ref([]);
const page = usePage();
const authUserId = page.props.auth.user.id;

const sendMessage = async () => {
    try {
        if (selectedUser.value) {
            form.receiver_id = selectedUser.value.id;
            // Send a POST request to the endpoint with the message data
            await axios.post("/sendMessage", {
                message: form.message,
                receiver_id: form.receiver_id,
            });
            // Optionally, clear the message input after sending
            form.message = "";
            await fetchMessages(selectedUser.value.id);
        }
    } catch (error) {
        console.error("Error sending message:", error);
    }
};

const fetchUsers = async () => {
    try {
        const response = await axios.get("/users");
        users.value = response.data.users;
    } catch (error) {
        console.error("Error fetching users:", error);
    }
};

const fetchMessages = async (userId) => {
    try {
        const response = await axios.get(`/messages/${userId}`);
        messages.value = response.data.messages;
    } catch (error) {
        console.error("Error fetching messages:", error);
    }
};

const selectUser = async (user) => {
    selectedUser.value = user;
    form.receiver_id = user.id;
    await fetchMessages(user.id);
};

onMounted(() => {
    fetchUsers();
    Echo.private(`chat.${authUserId}`).listen("MessageSent", (event) => {
        if (
            selectedUser.value &&
            (event.message.sender_id === selectedUser.value.id ||
                event.message.receiver_id === selectedUser.value.id)
        ) {
            messages.value.push(event.message.text);
        }
    });
});
onUnmounted(() => {
    Echo.leave(`chat.${authUserId}`);
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <VRow>
                <VCol cols="3">
                    <label>Select User:</label>
                    <div class="flex flex-col">
                        <button
                            v-for="user in users"
                            :key="user.id"
                            @click="selectUser(user)"
                            :class="{
                                'bg-blue-500 text-white': selectedUser === user,
                                'bg-gray-200': selectedUser !== user,
                            }"
                            class="p-2 m-1 rounded"
                        >
                            {{ user.name }}
                            {{ user.id === authUserId ? "(You)" : "" }}
                        </button>
                    </div>
                </VCol>
                <VCol cols="9" class="d-flex flex-column justify-center">
                    <div class="messages flex-grow overflow-y-auto mb-4">
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            :class="{
                                'text-right': message.sender_id === authUserId,
                                'text-left': message.sender_id !== authUserId,
                            }"
                            class="p-2"
                        >
                            <span
                                :class="{
                                    'bg-blue-100':
                                        message.sender_id === authUserId,
                                    'bg-gray-100':
                                        message.sender_id !== authUserId,
                                }"
                                class="p-2 rounded inline-block"
                            >
                                {{ message.text }}
                            </span>
                        </div>
                    </div>
                    <form @submit.prevent="sendMessage">
                        <input
                            type="text"
                            v-model="form.message"
                            placeholder="Type your message..."
                            required
                        />
                        <button type="submit">Send</button>
                    </form>
                </VCol>
            </VRow>
        </div>
    </AuthenticatedLayout>
</template>
