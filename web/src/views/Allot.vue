<template>
  <dev-article>
    <GeminiScrollbar class="xcon-split">
      <Row :gutter="6">
        <i-col span="6">
          <Card title="标的清单">
            <Tag color="green" slot="extra">的</Tag>
            <Row class="data-collect hidden-nowrap">总计：{{ dataCount.total }}</Row>
            <Divider size="small" dashed></Divider>
            <Progress :percent="dataCount.data_per" stroke-color="#19be6b" hide-info></Progress>
          </Card>
        </i-col>
        <i-col span="6">
          <Card title="税率测算">
            <Tag color="red" slot="extra">算</Tag>
            <Row class="data-collect hidden-nowrap">测算：{{ dataCount.count }}</Row>
            <Divider size="small" dashed></Divider>
            <Progress :percent="dataCount.count_per" stroke-color="#2db7f5" hide-info></Progress>
          </Card>
        </i-col>
        <i-col span="6">
          <Card title="测算反馈">
            <Tag color="geekblue" slot="extra">馈</Tag>
            <Row class="data-collect hidden-nowrap">反馈：{{ dataCount.back }}</Row>
            <Divider size="small" dashed></Divider>
            <Progress :percent="dataCount.back_per" stroke-color="#ff9900" hide-info></Progress>
          </Card>
        </i-col>
        <i-col span="6">
          <Card title="协作成果">
            <Tag color="blue" slot="extra">果</Tag>
            <Row class="data-collect hidden-nowrap">完成：{{ dataCount.backed }}</Row>
            <Divider size="small" dashed></Divider>
            <Progress :percent="dataCount.result_per" stroke-color="#ed4014" hide-info></Progress>
          </Card>
        </i-col>
      </Row>
      <Split v-model="split1" class="split margin-top8" min="600" max="350">
        <div slot="left" class="slot-left">
          <Tabs value="table">
            <TabPane label="任务标的" name="table">
              <Table
                :columns="cols"
                :data="datas"
                :loading="tableLoading"
                ref="selection"
                size="small"
                @on-current-change="selectChange"
                highlight-row
                border
                stripe
              ></Table>
              <Row class="margin-top16">
                <i-col class="hidden-nowrap align-right">
                  <Page
                    :total="ajaxs.length"
                    :page-size="pageSize"
                    :page-size-opts="[10, 20, 50, 100]"
                    show-sizer
                    transfer
                    @on-change="pageChange"
                    @on-page-size-change="sizeChange"
                  />
                </i-col>
              </Row>
            </TabPane>
            <!--表头附加相关操作：-->
            <template slot="extra">
              <Row class="hidden-nowrap">
                <RadioGroup v-model="dateType" @on-change="dateTypeChange">
                  <Radio label="day">今日</Radio>
                  <Radio label="week">周</Radio>
                  <Radio label="month">月</Radio>
                  <Radio label="year">年</Radio>
                </RadioGroup>
                <DatePicker
                  v-model="countDate"
                  type="daterange"
                  class="data-picker"
                  @on-change="dateChange"
                  transfer
                ></DatePicker>
                <Button class="margin-left8" type="primary" size="small" @click="countDateClick">查询</Button>
              </Row>
            </template>
          </Tabs>
        </div>
        <div slot="right" class="slot-right">
          <Tabs value="table">
            <TabPane label="任务分配" name="table">
              <div>
                <h2 style="text-align: center;">{{ current ? current.name :'税务案件任务分配'}}</h2>
                <br />
                <CellGroup>
                  <Cell title="税费测算">
                    <Select
                      class="multi-select"
                      v-model="examForm.count_user_id"
                      placeholder="人员选择..."
                      :disabled="examForm.count_dis"
                      @on-change="countChange"
                      transfer
                      slot="extra"
                    >
                      <Option v-for="item in users" :value="item.id" :key="item.id">{{ item.name }}</Option>
                    </Select>
                  </Cell>
                  <Cell title="测算复核">
                    <Select
                      class="multi-select"
                      v-model="examForm.counted_user_id"
                      placeholder="人员选择..."
                      :disabled="examForm.counted_dis"
                      @on-change="countedChange"
                      transfer
                      slot="extra"
                    >
                      <Option v-for="item in users" :value="item.id" :key="item.id">{{ item.name }}</Option>
                    </Select>
                  </Cell>

                  <div ref="teamdom" id="teamDom" class="mult-select multi-select-padding">案件审批</div>

                  <Cell title="文书制作">
                    <Select
                      class="multi-select"
                      v-model="examForm.docued_user_id"
                      placeholder="人员选择..."
                      :disabled="examForm.docued_dis"
                      @on-change="docuedChange"
                      transfer
                      slot="extra"
                    >
                      <Option v-for="item in users" :value="item.id" :key="item.id">{{ item.name }}</Option>
                    </Select>
                  </Cell>
                </CellGroup>
                <Divider></Divider>
                <h4 class="first-line" style="margin-bottom: 10px;">指定人员，则只能由被指定人员进行操作（要有对应岗位权限）；不指定人员，则由相关岗位人员执行操作。</h4>
                <h4 class="first-line" style="margin-bottom: 200px;">已完成的任务，不可变更操作人员信息。</h4>
              </div>
            </TabPane>
            <!--表头附加相关操作：-->
            <template slot="extra">
              <Row class="hidden-nowrap">
                <Button
                  type="primary"
                  size="small"
                  @click="allotExe"
                  v-if="current && !current.alloted"
                >执行</Button>
              </Row>
            </template>
          </Tabs>
        </div>
      </Split>
    </GeminiScrollbar>
  </dev-article>
</template>

<script>
import xcon from "../libs/xcon";

let teamDom = null;
let xSelect = window.xmSelect;

export default {
  name: "Allot",
  data() {
    return {
      // split
      split1: 0.7,

      // count
      ajax_count: null,

      // date
      dateType: "day",
      countDate: [new Date(), new Date()],

      // table
      cols: [
        {
          width: 55,
          type: "index",
          align: "center"
        },
        {
          title: "编号",
          key: "id"
        },
        {
          title: "标的名称",
          key: "name"
        },
        {
          title: "产权人",
          key: "owner"
        },
        {
          title: "产权性质",
          key: "area_type"
        },
        {
          title: "所属地区",
          key: "area_name"
        },
        {
          title: "是否分配",
          key: "allot",
          align: "center",
          render: (h, params) => {
            if (params.row.alloted)
              return h("Icon", {
                props: {
                  type: "ios-checkmark-circle-outline",
                  size: "24",
                  color: "#2d8cf0"
                }
              });
          }
        },
        {
          title: "是否测算",
          key: "count",
          align: "center",
          render: (h, params) => {
            if (params.row.count)
              return h("Icon", {
                props: {
                  type: "ios-checkmark-circle-outline",
                  size: "24",
                  color: "#2d8cf0"
                }
              });
          }
        },
        {
          title: "是否复核",
          key: "counted",
          align: "center",
          render: (h, params) => {
            if (params.row.counted)
              return h("Icon", {
                props: {
                  type: "ios-checkmark-circle-outline",
                  size: "24",
                  color: "#2d8cf0"
                }
              });
          }
        },
        {
          title: "是否审批",
          key: "teamed",
          align: "center",
          render: (h, params) => {
            if (params.row.teamed)
              return h("Icon", {
                props: {
                  type: "ios-checkmark-circle-outline",
                  size: "24",
                  color: "#2d8cf0"
                }
              });
          }
        },
        {
          title: "文书制作",
          key: "docued",
          align: "center",
          render: (h, params) => {
            if (params.row.docued)
              return h("Icon", {
                props: {
                  type: "ios-checkmark-circle-outline",
                  size: "24",
                  color: "#2d8cf0"
                }
              });
          }
        }
      ],
      ajaxs: [],
      pageIndex: 1,
      pageSize: 10,
      tableLoading: true,
      current: null,

      countLoading: false,
      count_cols: [
        {
          width: 55,
          type: "index",
          align: "center"
        },
        {
          title: "税种",
          key: "tax_name"
        },
        {
          title: "依据",
          key: "tax_base",
          align: "right"
        },
        {
          title: "税率",
          key: "tax_percent",
          align: "right"
        },
        {
          title: "税额",
          key: "tax_amount",
          align: "right"
        }
      ],
      counts: [],

      // users->select
      ajax_users: [],
      teamDom: null,
      showed: false
    };
  },
  methods: {
    countDateClick() {
      // 清除设置
      teamDom.reset();
      teamDom.update({ data: [] });
      this.current = null;
      this.tableLoading = true;

      let begin = xcon.dateFormat(this.countDate[0], "yyyy-MM-dd");
      let end = xcon.dateFormat(this.countDate[1], "yyyy-MM-dd");
      this.$.posts("/allot/find", { begin, end })
        .then(res => {
          this.ajaxs = res;
          this.tableLoading = false;
        })
        .catch(error => {
          this.tableLoading = false;
          this.$Message.error(error);
        });
    },
    dateChange(val) {
      this.dateType = "";
      this.countDate = val;
    },
    dateTypeChange(val) {
      const today = new Date().getTime();
      let date;
      switch (val) {
        case "day":
          date = today;
          break;
        case "week":
          date = today - 86400000 * 7;
          break;
        case "month":
          date = today - 86400000 * 30;
          break;
        case "year":
          date = today - 86400000 * 365;
          break;
      }
      this.countDate = [new Date(date), new Date(today)];
    },

    // page
    pageChange(index) {
      this.pageIndex = index;
    },
    sizeChange(size) {
      this.pageSize = size;
    },

    // 表格选择，查询分配用户
    selectChange(row) {
      this.current = row;
      // 必须要关闭下拉框，不然会触发数据侦听
      teamDom.closed();
      teamDom.reset();
      teamDom.update({ data: this.examForm.teamed_users });
    },

    // 测算用户选择
    countChange(value) {
      if (!value) return;

      const exam_id = xcon.exam.count;
      let { uid } = this.current;
      this.$.posts("/allot/exam", { uid, exam_id, user_id: value })
        .then(res => {
          xcon.arrsEdit(this.ajaxs, "uid", uid, res);

          this.current = res;
          this.$Message.success("测算任务已分配");
        })
        .catch(error => {
          this.$Message.error(error);
        });
    },
    // 复核用户选择
    countedChange(value) {
      if (!value) return;

      const exam_id = xcon.exam.counted;
      let { uid } = this.current;
      this.$.posts("/allot/exam", { uid, exam_id, user_id: value })
        .then(res => {
          xcon.arrsEdit(this.ajaxs, "uid", uid, res);

          this.current = res;
          this.$Message.success("复核任务已分配");
        })
        .catch(error => {
          this.$Message.error(error);
        });
    },
    // 文书用户选择
    docuedChange(value) {
      if (!value) return;

      const exam_id = xcon.exam.docued;
      let { uid } = this.current;
      this.$.posts("/allot/exam", { uid, exam_id, user_id: value })
        .then(res => {
          xcon.arrsEdit(this.ajaxs, "uid", uid, res);

          this.current = res;
          this.$Message.success("文书任务已分配");
        })
        .catch(error => {
          this.$Message.error(error);
        });
    },

    // 标的任务分配
    allotExe() {
      let row = this.current;
      if (row === null) {
        this.$Message.error("没有选择任务标的！");
        return;
      }

      this.$.posts("/allot/exec", { uid: row.uid })
        .then(res => {
          // 更新标的执行状态
          xcon.arrsEdit(this.ajaxs, "uid", row.uid, res);

          this.current = res;
          this.$Message.success("标的任务分配已执行");
        })
        .catch(error => {
          this.$Message.error(error);
        });
    }
  },
  computed: {
    dataCount() {
      if (this.ajax_count === null) {
        return {
          total: 0,
          count: 0,
          counted: 0,
          back: 0,
          backed: 0,
          data_per: 0,
          count_per: 0,
          back_per: 0,
          result_per: 0
        };
      } else {
        let { total, dataed, count, counted, back, backed } = this.ajax_count;
        let data_per = total > 0 ? (dataed / total) * 100 : 0;
        let count_per = dataed ? (counted / dataed) * 100 : 0;
        let back_per = counted ? (backed / counted) * 100 : 0;
        let result_per = total ? (backed / total) * 100 : 0;
        return {
          total,
          count,
          counted,
          back,
          backed,
          data_per,
          count_per,
          back_per,
          result_per
        };
      }
    },

    examForm() {
      // 计算点击标对应的用户审核记录
      let examConst = {
        count_user_id: "",
        counted_user_id: "",
        docued_user_id: "",
        teamed_users: [],
        count_dis: false,
        counted_dis: false,
        docued_dis: false
      };

      let row = this.current;
      if (row) {
        let count_dis = row.count === 1;
        let counted_dis = row.counted === 1;
        let docued_dis = row.docued === 1;

        let {
          count_user_id,
          counted_user_id,
          docued_user_id,
          teamed_user_ids,
          teamed_examed
        } = row;
        let user_ids = teamed_user_ids ? teamed_user_ids.split(",") : [];
        let exameds = teamed_examed ? teamed_examed.split(",") : [];

        // 修改列表状态
        let teamed_users = this.ajax_users.concat();
        teamed_users.forEach(item => {
          item.disabled = false;
          item.selected = false;
          for (let i = 0; i < user_ids.length; i++) {
            if (item.id === user_ids[i]) {
              item.selected = true;
              item.disabled = exameds[i] === "1";
              break;
            }
          }
        });
        return {
          count_user_id,
          counted_user_id,
          docued_user_id,
          teamed_users,
          count_dis,
          counted_dis,
          docued_dis
        };
      } else {
        return examConst;
      }
    },
    datas() {
      return xcon.pageData(this.ajaxs, this.pageIndex, this.pageSize);
    },
    users() {
      return this.current ? this.ajax_users : [];
    }
  },
  created() {
    this.$.gets("/allot/index")
      .then(res => {
        this.ajaxs = res.datas;
        this.ajax_users = res.users;
        this.ajax_count = res.count;
        this.tableLoading = false;
      })
      .catch(error => {
        this.tableLoading = false;
        this.$Message.error(error);
      });
  },
  mounted() {
    let that = this;
    teamDom = xSelect.render({
      el: "#teamDom",
      prop: {
        value: "id"
      },
      theme: {
        color: "#2d8cf0"
      },
      direction: 'down',
      data: [],
      model: {
        label: {
          type: "block",
          block: {
            //是否显示删除图标
            showIcon: false
          }
        }
      },
      on({ arr, change, isAdd }) {
        // 过滤掉重置数据
        if (arr.length === 0 && change.length === 0 && isAdd) return;
        // 没有打开，不发送数据
        if (!that.showed) return;

        const exam_id = xcon.exam.teamed;
        let { uid } = that.current;
        that.$.posts("/allot/team", {
          uid,
          exam_id,
          user_id: change[0].id,
          is_add: isAdd ? 1 : 0
        })
          .then(res => {
            xcon.arrsEdit(that.ajaxs, "uid", uid, res);

            that.current = res;
            that.$Message.success("审批任务已分配");
          })
          .catch(error => {
            that.$Message.error(error);
          });
      },
      hide() {
        that.showed = false;
      },
      show() {
        that.showed = true;
      }
    });
  }
};
</script>

<style scoped>
.multi-select {
  width: 180px;
}
.multi-select-padding {
  padding: 7px 16px;
}
.multi-select-margin {
  margin-bottom: 100px;
}
</style>