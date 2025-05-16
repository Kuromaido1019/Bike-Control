<template>
  <div>
    <button class="btn btn-secondary mb-3" @click="window.setShowUserCrud(false)">
      <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </button>
    <h2>Gestión de Usuarios (Vue)</h2>
    <button class="btn btn-primary mb-2" @click="showAdd = true">Nuevo Usuario</button>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Rol</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.id }}</td>
          <td>{{ user.name }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.role }}</td>
          <td>
            <button class="btn btn-info btn-sm" @click="editUser(user)">Editar</button>
            <button class="btn btn-danger btn-sm" @click="deleteUser(user.id)">Eliminar</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal para agregar usuario -->
    <div v-if="showAdd" class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.3)">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Agregar Usuario</h5>
            <button type="button" class="btn-close" @click="showAdd = false"></button>
          </div>
          <div class="modal-body">
            <input v-model="form.name" class="form-control mb-2" placeholder="Nombre">
            <input v-model="form.email" class="form-control mb-2" placeholder="Email">
            <select v-model="form.role" class="form-control mb-2">
              <option value="admin">Admin</option>
              <option value="guardia">Guardia</option>
              <option value="visitante">Visitante</option>
            </select>
            <input v-model="form.password" type="password" class="form-control mb-2" placeholder="Contraseña">
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showAdd = false">Cancelar</button>
            <button class="btn btn-primary" @click="addUser">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      users: [],
      showAdd: false,
      form: { name: '', email: '', role: 'visitante', password: '' },
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    fetchUsers() {
      fetch('/api/users')
        .then(res => res.json())
        .then(data => this.users = data);
    },
    addUser() {
      fetch('/api/users', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify(this.form)
      })
      .then(() => {
        this.showAdd = false;
        this.form = { name: '', email: '', role: 'visitante', password: '' };
        this.fetchUsers();
      });
    },
    deleteUser(id) {
      fetch(`/api/users/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
      })
      .then(() => this.fetchUsers());
    },
    editUser(user) {
      // Puedes implementar la edición aquí
      alert('Funcionalidad de edición no implementada aún');
    }
  }
}
</script>
