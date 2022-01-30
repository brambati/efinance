<template>
    <q-page class="row q-pa-md">
    
        <div class="col-md-4 q-mb-md">
            <q-btn color="primary" label="Adicionar Cidade" @click="cadastrar" />
        </div>
    
        <div class="col-md-12">
            <q-table title="Lista de Cidades" :rows="rows" :columns="columns" :pagination="pagination" row-key="name" ref="table" v-if="rows.length > 0">
                <template #body-cell-acao="props">
                      <q-td :props="props">
                        <q-btn-dropdown color="primary" label="Ação" dense>
                          <q-list>
                            <q-item clickable v-close-popup @click="atualizar(props.row.idCidade, props.row.cidade, props.row.idUF, props.row.estado)">
                              <q-item-section>
                                <q-item-label>Atualizar</q-item-label>
                              </q-item-section>
                            </q-item>
    
                            <q-item clickable v-close-popup @click="excluir(props.row.idCidade, props.row.cidade)">
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
          <div class="text-h6">{{!idCidade ? 'Cadastrar' : 'Atualizar'}} Cidade</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input v-model="idCidade" label="ID" v-if="idCidade" disable />
          <q-input v-model="cidade" label="Cidade" />
          <q-select v-model="estadoSelect" :options="listaEstado" label="Estado" />
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
  list: Array<{idCidade: number, cidade: string, idUF: number, estado: string}>
}

interface resposeDataEstado {
  status: string,
  message: string,
  list: Array<{id: number, estado: string, sigla: string}>
}


interface rowsType {
  idCidade: number, 
  cidade: string, 
  idUF: number,
  estado: string
}

interface listaEstado {
  value: number,
  label: string
}

export default defineComponent({
    name: 'PageCidade',
    components: {},
     
    data() {
      const $q = useQuasar();

      return {
          showDialog: ref(false),
          idCidade: 0,
          cidade: ref(''),
          listaEstado: [] as Array<listaEstado>,
          estadoSelect: {label: '', value: 0},
          pagination: {
              sortBy: 'desc',
              descending: false,
              page: 1,
              rowsPerPage: 100
          },
          rows: [] as Array<rowsType>,
          columns: [
              { name: 'acao', align: 'left', label: '', field: 'acao' },
              { name: 'idCidade', align: 'left', label: 'ID', field: 'idCidade', sortable: true },
              { name: 'cidade', align: 'left', label: 'Cidade', field: 'cidade', sortable: true },
              { name: 'estado', align: 'left', label: 'Estado', field: 'estado', sortable: true }
          ],
        };
    },

    async mounted() {
      await this.getData();    
    },

    methods: {
      async atualizar(id: number, cidade: string, idUF: number, estado: string) {
          this.idCidade     = id;
          this.cidade       = cidade;
          await this.getEstado();
          this.estadoSelect = {label: estado, value: idUF};
          this.showDialog = true
      },

      excluir(id: number, cidade: string) {
        this.$q.dialog({
          title: 'Ação Necessária',
          message: `Deseja realmente excluir a cidade <strong>${cidade}</strong>`,
          cancel: true,
          persistent: true,
          html: true
        }).onOk(() => {
          api.get(`/cidade/delete.php?id=${id}`)
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

        if(this.cidade.length <= 0) {
           this.showNotif('O campo cidade deve ser preenchido', 'negative');
           return;
        }
       

        if(this.estadoSelect.label.length <= 2) {
          this.showNotif('O campo estado deve ser preenchido', 'negative');
          return;
        }

        api.post('/cidade/save.php', { id: this.idCidade, cidade: this.cidade, estado: this.estadoSelect.value })
          .then((response) => {

            let data: resposeData = response.data as resposeData;

            if(data.status == 'success') {
              this.showNotif(data.message, 'grey-8');

              if(this.idCidade == 0) {
                this.idCidade = 0;
                this.cidade = '';
                this.estadoSelect = {label: '', value: 0};
              }
              void this.getData();
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

        await api.get('/cidade/get.php')
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

      async cadastrar() {
        await this.getEstado();        
        this.showDialog = true;
        this.idCidade = 0;
        this.cidade = '';
        this.estadoSelect = {label: '', value: 0};
      },

      async getEstado() { 
        this.$q.loading.show({});

        await api.get('/estado/get.php')
          .then((response) => {

            let data: resposeDataEstado = response.data as resposeDataEstado;

            if(data.status == 'success') {
              this.listaEstado = [];
              data.list.map(d => {
                this.listaEstado.push({label: d.estado, value: d.id})
              })
              this.$q.loading.hide();
            }

            if(data.status == 'error') {
              this.showNotif(data.message, 'negative');
            }
            
          }).catch(() => { 
            this.showNotif('Loading failed', 'negative'); 
          })
      },
    }
});
</script>

<style scoped>
.q-page {
    min-height: auto !important;
}
</style>