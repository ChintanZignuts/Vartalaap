<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const message = ref("");
const form = useForm({
    message: "",
});
const sendMessage = async () => {
    try {
        // Send a POST request to the endpoint with the message data
        await axios.post("/sendMessage", { message: form.message });
        // Optionally, clear the message input after sending
        form.message = "";
    } catch (error) {
        console.error("Error sending message:", error);
    }
};
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
            <div>
                <form @submit.prevent="sendMessage">
                    <input
                        type="text"
                        v-model="form.message"
                        placeholder="Type your message..."
                    />
                    <button type="submit">Send</button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
