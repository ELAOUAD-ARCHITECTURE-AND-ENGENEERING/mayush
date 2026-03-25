const SUPABASE_URL = 'https://ujacztwddfmkicfwppxz.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVqYWN6dHdkZGZta2ljZndwcHh6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjUyODA1MTEsImV4cCI6MjA4MDg1NjUxMX0.583t5sg0kqOMZBlBrK7HpINvqrYX3dbzPMlY5lJxBns';
window.supabaseClient = window.supabase && window.supabase.createClient
  ? window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY)
  : null;
