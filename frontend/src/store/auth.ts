import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface User {
  id: string;
  email: string;
  firstName: string;
  lastName: string;
  role: string;
}

interface Organization {
  id: string;
  name: string;
  slug: string;
  subscriptionTier: string;
}

interface AuthState {
  token: string | null;
  user: User | null;
  organization: Organization | null;
  isAuthenticated: boolean;
  setAuth: (token: string, user: User, organization: Organization) => void;
  logout: () => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      token: null,
      user: null,
      organization: null,
      isAuthenticated: false,
      setAuth: (token, user, organization) =>
        set({ token, user, organization, isAuthenticated: true }),
      logout: () =>
        set({ token: null, user: null, organization: null, isAuthenticated: false }),
    }),
    {
      name: 'auth-storage',
    }
  )
);
