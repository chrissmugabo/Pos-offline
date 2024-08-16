import { ref } from "vue";
import { useRouter } from "vue-router";
import { useHttp } from "@/composition-api/hooks/useHttp";

const user = ref(null);
const loggedUser = ref(null);
const { http } = useHttp("web");
const router = useRouter();
const isLoggedIn = localStorage.getItem("token");

export function useAuth() {
  const setCurrentUser = (row) => {
    user.value = row;
    loggedUser.value = row;
  };
  const logout = async () => {
    localStorage.clear();
    try {
      await http.post("logout");
      await router.replace({ name: "Login" });
    } catch (error) {
      window.location.replace("/");
    }
  };

  return { user, setCurrentUser, logout, isLoggedIn, loggedUser };
}
