<template>
    <q-page class="row q-pa-md">
    
        <div class="col-md-4 q-mb-md">
            <q-btn color="primary" label="Adicionar Estado" @click="cadastrar" /></div>
    
        <div class="col-md-12">
            <q-table title="Lista de Estados" :rows="rows" :columns="columns" :pagination="pagination" row-key="name" ref="table" v-if="rows.length > 0">
                <template #body-cell-acao="props">
                      <q-td :props="props">
                        <q-btn-dropdown color="primary" label="Ação" dense>
                          <q-list>
                            <q-item clickable v-close-popup @click="atualizar(props.row.id, props.row.estado, props.row.sigla)">
                              <q-item-section>
                                <q-item-label>Atualizar</q-item-label>
                              </q-item-section>
                            </q-item>
    
                            <q-item clickable v-close-popup @click="excluir(props.row.id, props.row.estado)">
                              <q-item-section>
                                <q-item-label>Excluir</q-item-label>
                              </q-item-section>
                            </q-item>
                          </q-list>
                        </q-btn-dropdown>
                      </q-td>
                </template>
          </q-table>
        </div>
    </q-page>


    <q-dialog v-model="showDialog">
      <q-card style="width: 700px; max-width: 80vw;">
        <q-card-section>
          <div class="text-h6">{{!idUF ? 'Cadastrar' : 'Atualizar'}} Estado</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input v-model="idUF" label="ID" v-if="idUF" disable />
          <q-input v-model="uf" label="Estado" />
          <q-input v-model="sigla" label="Sigla" :rules="[ val => val.length <= 2 || 'Utilize 2 characters para a sigla']" />
        </q-card-section>

        <q-card-actions align="right" class="bg-white text-teal">
          <q-btn label="Salvar" color="primary" @click="salvar" />
          <q-btn flat label="Fechar" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ref } from 'vue';
import { useQuasar } from 'quasar';
import { api } from 'boot/axios';

interface resposeData {
  status: string,
  message: string,
  list: Array<{id: number, estado: string, sigla: string}>
}

interface rowsType {
  id: number, 
  estado: string, 
  sigla: string
}

export default defineComponent({
    name: 'PageEstado',
    components: {},
     
    data() {
      const $q = useQuasar();

      return {
          showDialog: ref(false),
          idUF: 0,
          uf: ref(''),
          sigla: ref(''),
          pagination: {
              sortBy: 'desc',
              descending: false,
              page: 1,
              rowsPerPage: 100
          },
          rows: [] as Array<rowsType>,
          columns: [
              { name: 'acao', align: 'left', label: '', field: 'acao' },
              { name: 'id', align: 'left', label: 'ID', field: 'id', sortable: true },
              { name: 'estado', align: 'left', label: 'Estado', field: 'estado', sortable: true },
              { name: 'sigla', align: 'left', label: 'Sigla', field: 'sigla', sortable: true }
          ],
        };
    },

    async mounted() {
      await this.getData();    
    },

    methods: {
      atualizar(id: number, estado: string, sigla: string) {
          this.idUF   = id;
          this.uf = estado;
          this.sigla  = sigla;
          this.showDialog = true
      },

      excluir(id: number, estado: string) {
        this.$q.dialog({
          title: 'Ação Necessária',
          message: `Deseja realmente excluir o estado <strong>${estado}</strong>`,
          cancel: true,
          persistent: true,
          html: true
        }).onOk(() => {
          api.get(`/estado/delete.php?id=${id}`)
          .then((response) => {

            let data: resposeData = response.data as resposeData;

            if(data.status == 'success') {
              this.showNotif(data.message, 'grey-8');
              void this.getData();
            }

            if(data.status == 'error') {
              this.showNotif(data.message, 'negative');
            }
            
          }).catch(() => { 
            this.showNotif('Loading failed', 'negative'); 
          })
        }).onOk(() => {
          // console.log('>>>> second OK catcher')
        }).onCancel(() => {
          // console.log('>>>> Cancel')
        }).onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        })
      },

      salvar() {

        if(this.uf.length <= 0) {
           this.showNotif('O campo estado deve ser preenchido', 'negative');
           return;
        }

        if(this.sigla.length < 2 || this.sigla.length > 2) {
          this.showNotif('O campo sigle deve conter 2 caracteres', 'negative');
          return;
        }

        api.post('/estado/save.php', { id: this.idUF, uf: this.uf, sigla: this.sigla })
          .then((response) => {

            let data: resposeData = response.data as resposeData;

            if(data.status == 'success') {
              this.showNotif(data.message, 'grey-8');

              if(this.idUF == 0) {
                this.uf = '';
                this.sigla = '';
                this.idUF = 0;
                void this.getData();
              }
            }

            if(data.status == 'error') {
              this.showNotif(data.message, 'negative');
            }
            
          }).catch(() => { 
            this.showNotif('Loading failed', 'negative'); 
          })

      },

      showNotif (message: string, color: string) {
        this.$q.notify({
          color,
          message,
          position: 'center',
          multiLine: true,
        });
      },

      async getData() { 
        this.$q.loading.show({});

        await api.get('/estado/get.php')
          .then((response) => {

            let data: resposeData = response.data as resposeData;

            if(data.status == 'success') {
              this.rows = [];
              this.rows = data.list;
              this.$q.loading.hide();
            }

            if(data.status == 'error') {
              this.showNotif(data.message, 'negative');
            }
            
          }).catch(() => { 
            this.showNotif('Loading failed', 'negative'); 
          })
      },

      cadastrar() {
        this.showDialog = true;
        this.idUF = 0;
        this.uf = '';
        this.sigla = '';
      }
    }
});
</script>

<style scoped>
.q-page {
    min-height: auto !important;
}
</style>