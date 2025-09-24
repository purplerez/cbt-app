import { createSlice, createAsyncThunk, PayloadAction, Draft } from '@reduxjs/toolkit';
import { authService } from '@/services/auth';
import { User, DashboardExam } from '@/types';

interface AuthState {
     user: User | null;
     dashboardData: DashboardExam | null;
     token: string | null;
     isAuthenticated: boolean;
     isLoading: boolean;
     isError: boolean;
     errorMessage: string | null;
}

const initialState: AuthState = {
     user: null,
     dashboardData: null,
     token: typeof window !== 'undefined' ? localStorage.getItem('api_token') : null,
     isAuthenticated: false,
     isLoading: false,
     isError: false,
     errorMessage: null,
};

export const login = createAsyncThunk(
     'auth/login',
     async ({ email, password }: { email: string; password: string }) => {
          const response = await authService.login(email, password);
          return response;
     }
);

export const logout = createAsyncThunk('auth/logout', async () => {
     await authService.logout();
});

export const getCurrentUser = createAsyncThunk('auth/getCurrentUser', async () => {
     return await authService.getCurrentUser();
});

const authSlice = createSlice({
     name: 'auth',
     initialState,
     reducers: {
          clearError(state: Draft<AuthState>) {
               state.isError = false;
               state.errorMessage = null;
          },
          setToken(state: Draft<AuthState>, action: PayloadAction<string>) {
               state.token = action.payload;
               state.isAuthenticated = true;
          },
          clearAuth(state: Draft<AuthState>) {
               state.user = null;
               state.dashboardData = null;
               state.token = null;
               state.isAuthenticated = false;
          },
     },
     extraReducers: (builder) => {
          builder
               // Login
               .addCase(login.pending, (state: Draft<AuthState>) => {
                    state.isLoading = true;
                    state.isError = false;
                    state.errorMessage = null;
               })
               .addCase(login.fulfilled, (state: Draft<AuthState>, action: PayloadAction<{ user: User; token: string }>) => {
                    state.user = action.payload.user;
                    state.token = action.payload.token;
                    state.isAuthenticated = true;
                    state.isLoading = false;
               })
               .addCase(login.rejected, (state: Draft<AuthState>, action: { error: { message?: string } }) => {
                    state.isLoading = false;
                    state.isError = true;
                    state.errorMessage = action.error?.message || 'Login failed';
               })
               // Logout
               .addCase(logout.pending, (state: Draft<AuthState>) => {
                    state.isLoading = true;
               })
               .addCase(logout.fulfilled, (state: Draft<AuthState>) => {
                    state.user = null;
                    state.dashboardData = null;
                    state.token = null;
                    state.isAuthenticated = false;
                    state.isLoading = false;
               })
               .addCase(logout.rejected, (state: Draft<AuthState>) => {
                    state.isLoading = false;
                    state.isError = true;
                    state.errorMessage = 'Logout failed';
               })
               // Get Current User
               .addCase(getCurrentUser.pending, (state: Draft<AuthState>) => {
                    state.isLoading = true;
                    state.isError = false;
                    state.errorMessage = null;
               })
               .addCase(getCurrentUser.fulfilled, (state: Draft<AuthState>, action: PayloadAction<DashboardExam>) => {
                    state.dashboardData = action.payload;
                    state.isAuthenticated = true;
                    state.isLoading = false;
               })
               .addCase(getCurrentUser.rejected, (state: Draft<AuthState>, action: { error: { message?: string } }) => {
                    state.isLoading = false;
                    state.isError = true;
                    state.errorMessage = action.error?.message || 'Failed to get user data';
                    state.isAuthenticated = false;
               });
     },
});

export const { clearError, setToken, clearAuth } = authSlice.actions;

export default authSlice.reducer;
