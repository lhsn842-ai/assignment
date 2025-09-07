<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50">
    <div class="bg-white shadow-md rounded-2xl p-8 max-w-md w-full text-center">
      <h2 class="text-xl font-bold mb-4">Dashboard</h2>

      <div v-if="loading" class="text-gray-500">Loading...</div>

      <div v-else>
        <p class="mb-4">Welcome, <span class="font-semibold">{{ user?.name }}</span></p>

        <button
            @click="logout"
            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition"
        >
          Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const user = ref(null);
const loading = ref(true);

async function fetchUser() {
  const token = localStorage.getItem("authToken");
  if (!token) {
    router.push("/");
    return;
  }

  const query = `
    query {
      me {
        id
        name
        email
      }
    }
  `;

  try {
    const res = await fetch("/graphql", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: "Bearer " + token,
      },
      body: JSON.stringify({ query }),
    });

    const data = await res.json();

    if (data.errors) {
      localStorage.removeItem("authToken");
      router.push("/");
      return;
    }

    user.value = data.data.me;
  } catch (e) {
    localStorage.removeItem("authToken");
    router.push("/");
  } finally {
    loading.value = false;
  }
}

function logout() {
  localStorage.removeItem("authToken");
  router.push("/");
}

onMounted(fetchUser);
</script>
