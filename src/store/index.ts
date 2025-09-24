import { configureStore } from '@reduxjs/toolkit';
import examReducer from './examSlice';
import authReducer from './authSlice';

export const store = configureStore({
     reducer: {
          exam: examReducer,
          auth: authReducer,
     },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;