import React, { useState, useEffect } from "react";
import { Inertia } from "@inertiajs/inertia";
import { usePage } from "@inertiajs/inertia-react";
import Table from "react-bootstrap/Table";
import Row from "react-bootstrap/Row";

import { FiFrown, FiChevronLeft, FiPlus } from "react-icons/fi";

import Template from "../../components/Template";
import Link from "../../components/Link";
import Search from "../../components/Search";
import ToastMessage from "../../components/ToastMessage";

export default function ClassroomExam({ exams, classroom }) {
    const { flash } = usePage();
    const [listRegisters, setListRegisters] = useState(exams);
    const [showToast, setShowToast] = useState(false);

    function handleSubmit(status, values) {
        Inertia.post(route("classroom-exams-store"), values);
    }


    function handleFilter(search) {
        const excludeColumns = ["id"];
        const lowercasedValue = search.toLowerCase().trim();
        const results = exams.filter(function (item) {
            return Object.keys(item).some(key =>
                excludeColumns.includes(key)
                    ? false
                    : item[key]
                        .toString()
                        .toLowerCase()
                        .includes(lowercasedValue)
            );
        });
        setListRegisters(results);
    }



    useEffect(() => {
        setListRegisters(exams);
        setShowToast(flash.message ? true : false);
    }, [exams, flash]);


    return (
        <>
            <ToastMessage showToast={showToast} />
            <Template title="My classroom exams" subtitle="Teacher">
                <Link
                    classAtrributes="btn btn-secondary btn-new  mb-4"
                    tootip="List all classrooms"
                    placement="bottom"
                    tootip="List all classrooms"
                    text="List all classrooms"
                    icon={<FiChevronLeft />}
                    url={route("classrooms")}
                />

                <Link
                    classAtrributes="btn btn-primary btn-new  mb-4 ml-2"
                    tootip="Create new exam"
                    placement="bottom"
                    tootip="Create new exam"
                    text="Create new exam"
                    icon={<FiPlus />}
                    url={route("exams-create")}
                />

                <Search
                    placeholder="Enter your search to filter"
                    handleFunction={handleFilter}
                />

                {listRegisters && (
                    <Row>
                        <p className="col mt-2 text-center text-muted">
                            Showing <strong>{listRegisters.length}</strong> of
                            total <strong>{listRegisters.length}</strong>{" "}
                            record(s)
                        </p>
                    </Row>
                )}

                <Table striped bordered hover responsive="md" size="md">
                    <thead>
                        <tr>
                            <th className="text-center" style={{ width: "3%" }}>
                                #
                            </th>
                            <th
                                className="text-center text-uppercase"
                                style={{ width: "70%" }}
                            >
                                Description
                            </th>

                            <th className="text-center text-uppercase" style={{ width: "15%" }}>
                                Average
                            </th>

                            <th className="text-center text-uppercase">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {listRegisters.length == 0 && (
                            <tr>
                                <td
                                    colSpan="5"
                                    className="text-center text-muted"
                                >
                                    <FiFrown size={20} /> No records found
                                </td>
                            </tr>
                        )}
                        {listRegisters.map(register => (
                            <tr key={register.id} id={register.id}>
                                <td className="text-center">{register.id}</td>
                                <td>{register.title}</td>
                                <td className="text-center">{register.average}</td>
                                <td className="text-center">
                                    <Link
                                        classAtrributes={
                                            register.status == 0
                                                ? "btn btn-table btn-secondary mr-3"
                                                : "btn btn-table btn-success mr-3"
                                        }
                                        tootip={
                                            register.status == 0
                                                ? `Add ${register.title}`
                                                : `Remove ${register.title}`
                                        }
                                        placement="top"
                                        icon={
                                            register.status == 0
                                                ? "Add"
                                                : "Remove"
                                        }
                                        value={{ classroom: classroom, exam: register }}
                                        handleFunction={handleSubmit}
                                    />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </Table>
            </Template>
        </>
    );
}
